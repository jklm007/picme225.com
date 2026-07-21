<?php

namespace App\Services\DispatchEngine;

/**
 * GeoService - Service d'Optimisation Géographique
 *
 * Rôle : Réduire le coût des requêtes GPS en MySQL.
 *
 * Stratégie "Bounding Box" (Boîte de délimitation) :
 * -------------------------------------------------------
 * Au lieu de calculer Haversine sur 10 000 chauffeurs, on calcule d'abord
 * un carré (Nord/Sud/Est/Ouest) autour du point de recherche.
 * MySQL filtre via un index lat/lng ultra-rapide (<5ms).
 * On ne calcule l'Haversine précis que sur les ~30-50 chauffeurs restants.
 *
 * Gain de performance estimé : x20 à x100 selon le volume de données.
 */
class GeoService
{
    /**
     * Rayon de la Terre (en km)
     */
    const EARTH_RADIUS_KM = 6371.0;

    /**
     * Calcule une "Bounding Box" (carré) autour d'un point GPS.
     *
     * @param  float $lat       Latitude du centre
     * @param  float $lng       Longitude du centre
     * @param  float $radiusKm  Rayon de recherche en kilomètres
     * @return array            ['min_lat', 'max_lat', 'min_lng', 'max_lng']
     */
    public function getBoundingBox(float $lat, float $lng, float $radiusKm): array
    {
        // Conversion degrés → radians
        $latRad = deg2rad($lat);

        // 1 degré de latitude ≈ 111.32 km (constant)
        $deltaLat = $radiusKm / 111.32;

        // 1 degré de longitude varie selon la latitude
        $deltaLng = $radiusKm / (111.32 * cos($latRad));

        return [
            'min_lat' => $lat - $deltaLat,
            'max_lat' => $lat + $deltaLat,
            'min_lng' => $lng - $deltaLng,
            'max_lng' => $lng + $deltaLng,
        ];
    }

    /**
     * Calcule la distance précise entre deux points GPS (formule Haversine).
     *
     * @param  float $lat1  Latitude point 1
     * @param  float $lng1  Longitude point 1
     * @param  float $lat2  Latitude point 2
     * @param  float $lng2  Longitude point 2
     * @return float        Distance en kilomètres
     */
    public function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2)
           + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
           * sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return self::EARTH_RADIUS_KM * $c;
    }

    /**
     * Calcule l'angle (bearing) entre deux points GPS.
     * Utilisé pour le calcul du "Corridor Directionnel" (Mode HOME).
     *
     * @param  float $lat1  Latitude départ
     * @param  float $lng1  Longitude départ
     * @param  float $lat2  Latitude arrivée
     * @param  float $lng2  Longitude arrivée
     * @return float        Angle en degrés (0=Nord, 90=Est, 180=Sud, 270=Ouest)
     */
    public function bearing(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);
        $dLng    = deg2rad($lng2 - $lng1);

        $y = sin($dLng) * cos($lat2Rad);
        $x = cos($lat1Rad) * sin($lat2Rad)
           - sin($lat1Rad) * cos($lat2Rad) * cos($dLng);

        return fmod(rad2deg(atan2($y, $x)) + 360, 360);
    }

    /**
     * Vérifie si deux angles de direction sont "compatibles" (dans le même sens).
     * Utilisé pour le Mode HOME (la course va-t-elle dans le sens du domicile ?).
     *
     * @param  float $bearingTrip    Angle du trajet client (départ → arrivée)
     * @param  float $bearingHome    Angle vers le domicile du chauffeur
     * @param  float $tolerance      Tolérance en degrés (ex: 60° = demi-cercle avant)
     * @return bool
     */
    public function isSameDirection(float $bearingTrip, float $bearingHome, float $tolerance = 60.0): bool
    {
        $diff = abs($bearingTrip - $bearingHome);
        // Normaliser entre 0-180°
        if ($diff > 180) {
            $diff = 360 - $diff;
        }
        return $diff <= $tolerance;
    }

    /**
     * Vérifie la cohérence d'une position GPS (protection anti-fake GPS).
     * Si un chauffeur "saute" de plus de 200 km/h, sa position est ignorée.
     *
     * @param  float  $prevLat       Latitude précédente
     * @param  float  $prevLng       Longitude précédente
     * @param  float  $newLat        Nouvelle latitude
     * @param  float  $newLng        Nouvelle longitude
     * @param  int    $elapsedSeconds  Secondes écoulées entre les deux positions
     * @return bool                  true = position valide, false = position suspecte
     */
    public function isLocationPlausible(
        float $prevLat, float $prevLng,
        float $newLat, float $newLng,
        int $elapsedSeconds = 10
    ): bool {
        if ($elapsedSeconds <= 0) return true;

        $distanceKm  = $this->haversineDistance($prevLat, $prevLng, $newLat, $newLng);
        $elapsedHours = $elapsedSeconds / 3600.0;
        $speedKmH    = $distanceKm / $elapsedHours;

        // 300 km/h = vitesse max acceptable (marge pour les intervalles GPS courts)
        return $speedKmH <= 300.0;
    }

    /**
     * Génère un Geohash simple (4 caractères ≈ 40km², 5 chars ≈ 4km²).
     * Utilisé pour regrouper les chauffeurs en zones et les rooms Socket.io.
     *
     * @param  float $lat
     * @param  float $lng
     * @param  int   $precision  Nombre de caractères (4 ou 5 recommandés)
     * @return string
     */
    public function encode(float $lat, float $lng, int $precision = 5): string
    {
        $base32Chars = '0123456789bcdefghjkmnpqrstuvwxyz';
        $isEven      = true;
        $geohash     = '';
        $bits        = 0;
        $bitsTotal   = 0;
        $hashValue   = 0;
        $minLat      = -90.0;
        $maxLat      =  90.0;
        $minLng      = -180.0;
        $maxLng      =  180.0;

        while (strlen($geohash) < $precision) {
            if ($isEven) {
                $mid = ($minLng + $maxLng) / 2;
                if ($lng >= $mid) { $hashValue = ($hashValue << 1) + 1; $minLng = $mid; }
                else              { $hashValue = ($hashValue << 1) + 0; $maxLng = $mid; }
            } else {
                $mid = ($minLat + $maxLat) / 2;
                if ($lat >= $mid) { $hashValue = ($hashValue << 1) + 1; $minLat = $mid; }
                else              { $hashValue = ($hashValue << 1) + 0; $maxLat = $mid; }
            }
            $isEven    = !$isEven;
            $bitsTotal = ++$bits;

            if ($bits === 5) {
                $geohash  .= $base32Chars[$hashValue];
                $bits      = 0;
                $hashValue = 0;
            }
        }

        return $geohash;
    }
}
