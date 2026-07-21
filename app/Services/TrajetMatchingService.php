<?php

namespace App\Services;

use App\Models\Trip;
use App\Models\Intention;
use App\Models\TripMatch;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Service de Matching BlaBlaCar-style pour Abidjan.
 * 
 * Calcule la compatibilité entre les Offres (Trips) et les Demandes (Intentions).
 */
class TrajetMatchingService
{
    private const MAX_PICKUP_RADIUS_KM = 5.0; // Plus large pour Abidjan
    private const MAX_DIRECTION_DEVIATION_DEG = 30.0; // Plus strict pour le corridor

    /**
     * Trouve des trajets (offres) correspondant à une intention (demande).
     */
    public function findMatchesForIntention(Intention $intention): Collection
    {
        $trips = Trip::with(['user:id,first_name,last_name,picture,social_rating'])
            ->where('status', 'OPEN')
            ->where('seats_available', '>=', $intention->seats_needed)
            ->where('departure_time', '>=', $intention->earliest_departure->subHours(1))
            ->where('departure_time', '<=', $intention->latest_departure->addHours(1))
            ->get();

        return $trips->map(function ($trip) use ($intention) {
            $score = $this->calculateScore($intention, $trip);
            if ($score >= 40) { // Seuil minimal de pertinence
                return [
                    'trip' => $trip,
                    'score' => $score,
                    'distance_km' => $this->haversineDistance($intention->origin_lat, $intention->origin_lng, $trip->origin_lat, $trip->origin_lng)
                ];
            }
            return null;
        })->filter()->sortByDesc('score')->values();
    }

    /**
     * Calcule un score de 0 à 100.
     */
    private function calculateScore($intention, $trip): int
    {
        // 1. Proximité Départ (40 pts)
        $distStart = $this->haversineDistance($intention->origin_lat, $intention->origin_lng, $trip->origin_lat, $trip->origin_lng);
        $scoreStart = max(0, 40 * (1 - ($distStart / self::MAX_PICKUP_RADIUS_KM)));

        // 2. Proximité Arrivée (40 pts)
        $distEnd = $this->haversineDistance($intention->destination_lat, $intention->destination_lng, $trip->destination_lat, $trip->destination_lng);
        $scoreEnd = max(0, 40 * (1 - ($distEnd / self::MAX_PICKUP_RADIUS_KM)));

        // 3. Timing (20 pts)
        $timeDiff = abs($intention->earliest_departure->diffInMinutes($trip->departure_time));
        $scoreTime = max(0, 20 * (1 - ($timeDiff / 120))); // Sur une plage de 2h

        return (int) ($scoreStart + $scoreEnd + $scoreTime);
    }

    public function haversineDistance($lat1, $lng1, $lat2, $lng2): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) * sin($dLng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }
}
