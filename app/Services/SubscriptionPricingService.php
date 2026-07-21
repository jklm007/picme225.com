<?php

namespace App\Services;

use App\Models\ServiceType;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * SubscriptionPricingService
 *
 * Calculates the real dynamic monthly price for a recurring commute subscription.
 * Uses OSRM road-distance routing instead of the imprecise Haversine (as-the-crow-flies).
 *
 * Formula:
 *   Monthly Price = (Trip Price Aller + Trip Price Retour?) × Active Days/Month × (1 - Discount%)
 *
 * Where:
 *   Trip Price = service_type.fixed + (chargeable_km × service_type.price) + (minutes × service_type.minute)
 *   Active Days/Month = days_per_week × 4.33
 *   Discount = configurable discount for subscriptions (default 15%)
 */
class SubscriptionPricingService
{
    /** Default subscription loyalty discount percentage */
    const DEFAULT_DISCOUNT_PERCENT = 15;

    /** Minimum monthly price floor in CFA */
    const MIN_MONTHLY_PRICE = 5000;

    /** Minimum single-trip price floor in CFA */
    const MIN_TRIP_PRICE = 500;

    /**
     * Calculate the dynamic monthly price for a commute subscription.
     *
     * @param int         $serviceTypeId   ID of the ServiceType (vehicle category)
     * @param float       $sLat            Pickup latitude
     * @param float       $sLng            Pickup longitude
     * @param float       $dLat            Drop-off latitude
     * @param float       $dLng            Drop-off longitude
     * @param array       $activeDays      Array of day strings: ['MON','TUE','WED','THU','FRI']
     * @param string|null $returnTime      If set, include a return trip in the price
     * @param float|null  $discountPercent Override discount (null = use default)
     * @param array|null  $waypoints       Intermediate stops [{latitude, longitude, address}]
     * @return array{
     *   distance_km: float,
     *   duration_mins: int,
     *   single_trip_price: float,
     *   daily_price: float,
     *   days_per_month: int,
     *   original_monthly_price: float,
     *   discounted_monthly_price: float,
     *   discount_percent: float,
     *   has_return_trip: bool,
     *   waypoints_count: int,
     *   currency: string,
     *   routing_method: string,
     * }
     */
    public function calculate(
        int $serviceTypeId,
        float $sLat,
        float $sLng,
        float $dLat,
        float $dLng,
        array $activeDays,
        ?string $returnTime = null,
        ?float $discountPercent = null,
        ?array $waypoints = null
    ): array {
        /** @var ServiceType $serviceType */
        $serviceType = ServiceType::findOrFail($serviceTypeId);

        // ─── 1. Road distance via OSRM (with waypoints if provided) ─────────
        $routingMethod  = 'osrm';
        $waypointsCount = is_array($waypoints) ? count($waypoints) : 0;
        $routing = get_osrm_routing($sLat, $sLng, $dLat, $dLng, $waypointsCount > 0 ? $waypoints : null);

        if ($routing) {
            $distanceKm  = round($routing['distance'] / 1000, 2);
            $durationMins = (int) round($routing['duration'] / 60);
        } else {
            // Fallback: Haversine with +25% urban road factor
            Log::warning('[SubscriptionPricingService] OSRM unavailable, using Haversine fallback');
            $routingMethod  = 'haversine_corrected';
            $rawDistanceKm  = $this->haversine($sLat, $sLng, $dLat, $dLng);
            // Add estimated waypoints distance (approx 3km per stop if OSRM unavailable)
            if ($waypointsCount > 0) $rawDistanceKm += $waypointsCount * 3.0;
            $distanceKm     = round($rawDistanceKm * 1.25, 2); // +25% road factor
            $durationMins   = (int) round(($distanceKm / 30) * 60); // 30 km/h urban avg
        }

        if ($distanceKm < 0.5) $distanceKm = 0.5;
        if ($durationMins < 2)  $durationMins = 2;

        // ─── 2. Single trip price ─────────────────────────────────────────
        $singleTripPrice = $this->computeTripPrice($serviceType, $distanceKm, $durationMins);

        // ─── 3. Daily price (aller + retour if applicable) ───────────────
        $hasReturnTrip = !empty($returnTime);
        $dailyPrice    = $hasReturnTrip ? $singleTripPrice * 2 : $singleTripPrice;

        // ─── 4. Days per month ────────────────────────────────────────────
        $daysPerWeek  = count(array_unique($activeDays));
        $daysPerMonth = (int) round($daysPerWeek * 4.33);
        if ($daysPerMonth < 1) $daysPerMonth = 1;

        // ─── 5. Monthly price ─────────────────────────────────────────────
        $originalMonthlyPrice = $dailyPrice * $daysPerMonth;

        $discount = $discountPercent ?? (float) \Setting::get('subscription_discount_percent', self::DEFAULT_DISCOUNT_PERCENT);
        $discount = max(0, min(50, $discount)); // clamp 0–50%

        $discountedMonthlyPrice = $originalMonthlyPrice * (1 - $discount / 100);
        $discountedMonthlyPrice = max(self::MIN_MONTHLY_PRICE, round($discountedMonthlyPrice, -2)); // round to nearest 100 CFA

        return [
            'distance_km'             => $distanceKm,
            'duration_mins'           => $durationMins,
            'single_trip_price'       => round($singleTripPrice, 2),
            'daily_price'             => round($dailyPrice, 2),
            'days_per_month'          => $daysPerMonth,
            'original_monthly_price'  => round($originalMonthlyPrice, 2),
            'discounted_monthly_price'=> $discountedMonthlyPrice,
            'discount_percent'        => $discount,
            'has_return_trip'         => $hasReturnTrip,
            'waypoints_count'         => $waypointsCount,
            'currency'                => \Setting::get('currency', 'CFA'),
            'routing_method'          => $routingMethod,
        ];
    }

    /**
     * Compute the price for one trip using the ServiceType tariff rules.
     */
    private function computeTripPrice(ServiceType $serviceType, float $distanceKm, int $durationMins): float
    {
        $basePrice = (float) $serviceType->fixed;

        // Deduct free km included in base price
        $freeKm = (float) ($serviceType->distance ?? 0);
        $chargeableKm = max(0, $distanceKm - $freeKm);

        $calculator = strtoupper($serviceType->calculator ?? 'DISTANCE');

        $variablePrice = match ($calculator) {
            'MIN'          => $durationMins * (float) $serviceType->minute,
            'HOUR'         => (float) $serviceType->minute * 60,
            'DISTANCEMIN'  => ($chargeableKm * (float) $serviceType->price)
                              + ($durationMins * (float) $serviceType->minute),
            'DISTANCEHOUR' => ($chargeableKm * (float) $serviceType->price)
                              + ((float) $serviceType->minute * $durationMins * 60),
            default        => $chargeableKm * (float) $serviceType->price, // DISTANCE
        };

        return max(self::MIN_TRIP_PRICE, $basePrice + $variablePrice);
    }

    /**
     * Haversine great-circle distance in km.
     */
    private function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2
           + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
