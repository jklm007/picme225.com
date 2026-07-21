<?php

namespace App\Services;

use App\Helpers\Helper;
use App\Models\ServiceType;
use Illuminate\Support\Arr;

class SharedTripService
{
    public const DEFAULT_STOP_DISTANCE_KM = 2.0;
    private const AVERAGE_CITY_SPEED_KMH = 25;

    /**
     * Normalise un tableau de segments en s'assurant que toutes les clefs existent.
     *
     * @param  array|string  $segmentsInput
     * @return array
     */
    public static function normalizeSegments($segmentsInput): array
    {
        if (is_string($segmentsInput)) {
            $segments = json_decode($segmentsInput, true) ?? [];
        } else {
            $segments = $segmentsInput ?? [];
        }

        return collect($segments)->map(function ($segment) {
            return [
                's_latitude' => (float) Arr::get($segment, 's_latitude'),
                's_longitude' => (float) Arr::get($segment, 's_longitude'),
                's_address' => Arr::get($segment, 's_address'),
                'd_latitude' => (float) Arr::get($segment, 'd_latitude'),
                'd_longitude' => (float) Arr::get($segment, 'd_longitude'),
                'd_address' => Arr::get($segment, 'd_address'),
                'price' => (float) Arr::get($segment, 'price', 0),
                'user_id' => Arr::get($segment, 'user_id'),
                'segment_name' => Arr::get($segment, 'segment_name'),
            ];
        })->filter(function ($segment) {
            return $segment['s_latitude'] !== 0.0 && $segment['s_longitude'] !== 0.0
                && $segment['d_latitude'] !== 0.0 && $segment['d_longitude'] !== 0.0;
        })->values()->all();
    }

    public static function totalDistanceKm(array $segments): float
    {
        return collect($segments)->reduce(function ($carry, $segment) {
            $distance = Helper::haversineGreatCircleDistance(
                $segment['s_latitude'],
                $segment['s_longitude'],
                $segment['d_latitude'],
                $segment['d_longitude']
            );

            return $carry + ($distance / 1000);
        }, 0.0);
    }

    public static function hydrateSegmentsWithEstimates(array $segments): array
    {
        return collect($segments)->map(function ($segment) {
            $distanceKm = Helper::haversineGreatCircleDistance(
                $segment['s_latitude'],
                $segment['s_longitude'],
                $segment['d_latitude'],
                $segment['d_longitude']
            ) / 1000;

            $segment['distance_km'] = round($distanceKm, 2);
            $segment['duration_min'] = round(self::estimateTravelMinutes($distanceKm), 0);

            return $segment;
        })->all();
    }

    public static function estimateTravelMinutes(float $distanceKm): float
    {
        if ($distanceKm <= 0) {
            return 0;
        }

        return ($distanceKm / self::AVERAGE_CITY_SPEED_KMH) * 60;
    }

    public static function estimateFare(ServiceType $serviceType, array $segments, int $passengerCount = 1): array
    {
        $distanceKm = self::totalDistanceKm($segments);
        $freeKm = max(0, (int) $serviceType->free_km_per_passenger);
        
        // Calcul du prix total sans réduction
        $baseFare = (float) $serviceType->fixed;
        $fullDistanceFare = $distanceKm * (float) $serviceType->price;
        $segmentFare = count($segments) * (float) $serviceType->price_per_segment;
        
        $rawTotalFare = $baseFare + $fullDistanceFare + $segmentFare;
        
        // Application de la logique des km gratuits
        if ($distanceKm <= $freeKm) {
            // Trajet "gratuit" (paiement du tarif de base uniquement)
            $farePerPassenger = $baseFare;
            $payableKm = 0;
        } else {
            // Réduction proportionnelle
            // Formule : PrixFinal = PrixTotal * ((DistanceTotal - KmGratuits) / DistanceTotal)
            $ratio = ($distanceKm - $freeKm) / $distanceKm;
            $calculatedFare = $rawTotalFare * $ratio;
            
            // On s'assure que le prix ne descend jamais en dessous du tarif de base
            $farePerPassenger = max($baseFare, $calculatedFare);
            $payableKm = $distanceKm - $freeKm;
        }

        return [
            'distance_km' => round($distanceKm, 2),
            'payable_distance_km' => round($payableKm, 2),
            'base_fare' => round($baseFare, 2),
            'distance_fare' => round($fullDistanceFare, 2), // On affiche le tarif distance complet pour info
            'segment_fare' => round($segmentFare, 2),
            'raw_total_fare' => round($rawTotalFare, 2),
            'fare_per_passenger' => round($farePerPassenger, 2),
            'passenger_count' => $passengerCount,
            'free_km' => $freeKm,
        ];
    }

    public static function maxDetour(ServiceType $serviceType): int
    {
        if ($serviceType->sharing_type === 'PDP' && $serviceType->max_detour_communal) {
            return (int) $serviceType->max_detour_communal;
        }

        if ($serviceType->max_detour_intercommunal) {
            return (int) $serviceType->max_detour_intercommunal;
        }

        return $serviceType->max_detour_communal ?: 10;
    }

    public static function maxStopDistanceKm(): float
    {
        return self::DEFAULT_STOP_DISTANCE_KM;
    }
}

