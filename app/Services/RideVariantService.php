<?php

namespace App\Services;

use App\Models\PdpStop;
use Illuminate\Support\Facades\Log;

class RideVariantService
{
    /**
     * Validate if coordinates are near a valid public stop
     *
     * @param float $latitude
     * @param float $longitude
     * @param float $maxDistanceMeters
     * @return array|null Returns stop data if valid, null otherwise
     */
    public function validateStopLocation($latitude, $longitude, $maxDistanceMeters = null)
    {
        $maxDistance = $maxDistanceMeters ?? config('ride_variants.stops.validation.max_distance_meters', 100);

        // Build query for valid stops
        $query = PdpStop::where('is_active', true);

        if (config('ride_variants.stops.validation.require_approved', true)) {
            $query->where('status', 'APPROVED');
        }

        if (config('ride_variants.stops.validation.require_public', true)) {
            $query->where('is_public', true);
        }

        // Find nearest stop using Haversine formula
        $earthRadius = 6371000; // meters

        $query->selectRaw(
            "*, 
            (? * acos(
                cos(radians(?)) * cos(radians(latitude)) * 
                cos(radians(longitude) - radians(?)) + 
                sin(radians(?)) * sin(radians(latitude))
            )) AS distance",
            [$earthRadius, $latitude, $longitude, $latitude]
        )
            ->having('distance', '<=', $maxDistance)
            ->orderBy('distance', 'asc');

        $nearestStop = $query->first();

        if ($nearestStop) {
            Log::info('Stop validation successful', [
                'stop_id' => $nearestStop->id,
                'stop_name' => $nearestStop->name,
                'distance' => round($nearestStop->distance, 2) . 'm'
            ]);

            return [
                'valid' => true,
                'stop' => $nearestStop,
                'distance' => $nearestStop->distance,
            ];
        }

        Log::warning('No valid stop found', [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'max_distance' => $maxDistance
        ]);

        return null;
    }

    /**
     * Find nearby public stops
     *
     * @param float $latitude
     * @param float $longitude
     * @param int $radiusMeters
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function findNearbyStops($latitude, $longitude, $radiusMeters = null, $limit = null)
    {
        $radius = $radiusMeters ?? config('ride_variants.stops.search.radius_meters', 500);
        $maxResults = $limit ?? config('ride_variants.stops.search.max_results', 10);

        $earthRadius = 6371000; // meters

        $stops = PdpStop::where('is_active', true)
            ->where('status', 'APPROVED')
            ->where('is_public', true)
            ->selectRaw(
                "*, 
                (? * acos(
                    cos(radians(?)) * cos(radians(latitude)) * 
                    cos(radians(longitude) - radians(?)) + 
                    sin(radians(?)) * sin(radians(latitude))
                )) AS distance",
                [$earthRadius, $latitude, $longitude, $latitude]
            )
            ->having('distance', '<=', $radius)
            ->orderBy('distance', 'asc')
            ->limit($maxResults)
            ->get();

        return $stops;
    }

    /**
     * Validate detour constraints for dynamic rides
     *
     * @param array $originalRoute ['distance' => km, 'duration' => seconds]
     * @param array $detourRoute ['distance' => km, 'duration' => seconds]
     * @return array Validation result
     */
    public function validateDetourConstraints($originalRoute, $detourRoute)
    {
        $constraints = config('ride_variants.variants.dynamique.constraints');

        $originalDistanceKm = $originalRoute['distance'];
        $originalDurationMin = $originalRoute['duration'] / 60;

        $detourDistanceKm = $detourRoute['distance'];
        $detourDurationMin = $detourRoute['duration'] / 60;

        // Calculate additional distance and time
        $additionalDistanceKm = $detourDistanceKm - $originalDistanceKm;
        $additionalTimeMin = $detourDurationMin - $originalDurationMin;

        // Calculate percentage increase
        $distanceIncreasePercent = ($additionalDistanceKm / $originalDistanceKm) * 100;

        $violations = [];

        // Check minimum trip distance
        if ($originalDistanceKm < $constraints['min_direct_distance_km']) {
            $violations[] = [
                'type' => 'min_distance',
                'message' => "Trip too short for dynamic ride. Minimum: {$constraints['min_direct_distance_km']}km",
                'value' => $originalDistanceKm,
                'limit' => $constraints['min_direct_distance_km']
            ];
        }

        $maxAdditionalDistance = \Setting::get('detour_max_distance_km', 5);
        $maxAdditionalTime = \Setting::get('detour_max_time_mins', 15);
        $maxPerentage = \Setting::get('detour_max_percentage', 30);

        // Check max additional distance
        if ($additionalDistanceKm > $maxAdditionalDistance) {
            $violations[] = [
                'type' => 'max_detour_distance',
                'message' => "Detour distance exceeds limit: {$maxAdditionalDistance}km",
                'value' => round($additionalDistanceKm, 2),
                'limit' => $maxAdditionalDistance
            ];
        }

        // Check max additional time
        if ($additionalTimeMin > $maxAdditionalTime) {
            $violations[] = [
                'type' => 'max_detour_time',
                'message' => "Detour time exceeds limit: {$maxAdditionalTime} minutes",
                'value' => round($additionalTimeMin, 2),
                'limit' => $maxAdditionalTime
            ];
        }

        // Check max percentage increase
        if ($distanceIncreasePercent > $maxPerentage) {
            $violations[] = [
                'type' => 'max_detour_percentage',
                'message' => "Detour exceeds {$maxPerentage}% of original route",
                'value' => round($distanceIncreasePercent, 2),
                'limit' => $maxPerentage
            ];
        }

        $isValid = empty($violations);

        if (!$isValid) {
            Log::warning('Detour validation failed', [
                'original' => $originalRoute,
                'detour' => $detourRoute,
                'violations' => $violations
            ]);
        }

        return [
            'valid' => $isValid,
            'violations' => $violations,
            'metrics' => [
                'additional_distance_km' => round($additionalDistanceKm, 2),
                'additional_time_min' => round($additionalTimeMin, 2),
                'distance_increase_percent' => round($distanceIncreasePercent, 2),
            ]
        ];
    }

    /**
     * Calculate detour route using OSRM with waypoint
     *
     * @param float $startLat
     * @param float $startLng
     * @param float $waypointLat
     * @param float $waypointLng
     * @param float $endLat
     * @param float $endLng
     * @return array|null Route data or null on failure
     */
    public function calculateDetourRoute($startLat, $startLng, $waypointLat, $waypointLng, $endLat, $endLng)
    {
        try {
            $baseUrl = "https://router.project-osrm.org/route/v1/driving/";

            // OSRM uses lng,lat format
            $coordinates = "{$startLng},{$startLat};{$waypointLng},{$waypointLat};{$endLng},{$endLat}";
            $url = $baseUrl . $coordinates . "?overview=false&steps=false";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_USERAGENT, 'PickMePro/1.0');

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200) {
                throw new \Exception("OSRM HTTP error: {$httpCode}");
            }

            $data = json_decode($response, true);

            if (!isset($data['code']) || $data['code'] !== 'Ok' || !isset($data['routes'][0])) {
                throw new \Exception('Invalid OSRM response');
            }

            return [
                'distance' => $data['routes'][0]['distance'] / 1000, // Convert to km
                'duration' => $data['routes'][0]['duration'],        // seconds
            ];

        } catch (\Exception $e) {
            Log::error('OSRM detour calculation failed', [
                'error' => $e->getMessage(),
                'coordinates' => compact('startLat', 'startLng', 'waypointLat', 'waypointLng', 'endLat', 'endLng')
            ]);

            // Fallback to Haversine estimation
            if (config('ride_variants.dynamic_matching.detour_calculation.fallback_to_haversine', true)) {
                return $this->calculateDetourRouteFallback($startLat, $startLng, $waypointLat, $waypointLng, $endLat, $endLng);
            }

            return null;
        }
    }

    /**
     * Fallback detour calculation using Haversine
     */
    private function calculateDetourRouteFallback($startLat, $startLng, $waypointLat, $waypointLng, $endLat, $endLng)
    {
        $dist1 = $this->haversineDistance($startLat, $startLng, $waypointLat, $waypointLng);
        $dist2 = $this->haversineDistance($waypointLat, $waypointLng, $endLat, $endLng);

        $totalDistance = $dist1 + $dist2;
        $estimatedDuration = ($totalDistance / 40) * 3600; // 40 km/h average

        return [
            'distance' => $totalDistance,
            'duration' => $estimatedDuration,
        ];
    }

    /**
     * Calculate distance using Haversine formula
     */
    private function haversineDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371; // km

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lng1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lng2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos($latFrom) * cos($latTo) *
            sin($lonDelta / 2) * sin($lonDelta / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Get variant multiplier (for prive)
     *
     * @param string $variant
     * @return float
     */
    public function getVariantMultiplier($variant)
    {
        if ($variant == 'prive') {
            return (float) \Setting::get('prive_variant_multiplier', 1.2);
        }

        return 1.0;
    }

    public function getVariantDiscount($variant)
    {
        if ($variant == 'arret') {
            return (float) \Setting::get('arret_variant_discount', 10);
        }

        return 0;
    }

    /**
     * Apply variant discount or multiplier to price
     *
     * @param float $basePrice
     * @param string $variant
     * @return float
     */
    public function applyVariantDiscount($basePrice, $variant)
    {
        if ($variant == 'prive') {
            $multiplier = $this->getVariantMultiplier($variant);
            return $basePrice * $multiplier;
        }

        $discountPercent = $this->getVariantDiscount($variant);
        $discount = ($discountPercent / 100) * $basePrice;
        return $basePrice - $discount;
    }
}
