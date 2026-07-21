<?php

namespace App\Services;

use App\Models\Provider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * FraudDetectionService — PicMe V2.3
 *
 * Calcule le MovementIntegrityScore (MIS) d'un chauffeur à partir de
 * son flux GPS. MIS élevé = chauffeur légitime. MIS bas = suspect.
 *
 * MIS = 0.5 × Stability + 0.3 × Velocity + 0.2 × Consistency
 *
 * Niveaux d'action :
 *   MIS > 80  → STATUS_CLEAN      (score dispatch intact)
 *   MIS 61-80 → MONITORING        (log seulement, score intact)
 *   MIS 31-60 → DISPATCH_REDUCED  (score réduit de 50%)
 *   MIS ≤ 30  → FREEZE_REVIEW     (exclusion temporaire)
 */
class FraudDetectionService
{
    // Constantes de détection
    const URBAN_SPEED_LIMIT_KMH    = 130;    // Vitesse max urbaine plausible
    const TELEPORT_DISTANCE_KM     = 5.0;    // Distance de téléportation suspecte
    const TELEPORT_TIME_SECONDS    = 10;     // En moins de N secondes
    const JITTER_STDDEV_THRESHOLD  = 0.0005; // Écart-type GPS suspect (deg)
    const CACHE_TTL_MINUTES        = 30;     // TTL du score en cache

    /**
     * Calcule et met en cache le MIS d'un chauffeur à partir de ses derniers pings GPS.
     *
     * @param  Provider $provider
     * @param  array    $pings  Liste de pings : [['lat','lng','speed_kmh','accuracy_meters',
     *                           'is_mock_location','sensor_timestamp','device_fingerprint_hash']]
     * @return float   MIS de 0 à 100
     */
    public function computeMIS(Provider $provider, array $pings): float
    {
        if (empty($pings) || count($pings) < 2) {
            // Pas assez de données → score neutre, pas de pénalité
            return 80.0;
        }

        $stability   = 0.0;
        $velocity    = 0.0;
        $consistency = 0.0;

        // Détection immédiate de fraude flagrante (Mock Location / Fake GPS)
        $mockCount = collect($pings)->where('is_mock_location', true)->count();
        if ($mockCount > 0) {
            Log::warning("[FraudDetection] mock_location détecté ({$mockCount} pings) pour Chauffeur #{$provider->id} -> MIS forcée à 0.0");
            $mis = 0.0;
        } else {
            $stability   = $this->analyzeStability($pings);
            $velocity    = $this->analyzeVelocity($pings);
            $consistency = $this->analyzeConsistency($pings, $provider);

            // Si téléportation extrême détectée
            if ($velocity === 0.0) {
                $mis = 0.0;
            } else {
                $mis = (0.5 * $stability) + (0.3 * $velocity) + (0.2 * $consistency);
            }
        }
        $mis = round(min(100.0, max(0.0, $mis)), 2);

        // Stocker en Redis (non bloquant)
        Cache::put("mis_{$provider->id}", $mis, now()->addMinutes(self::CACHE_TTL_MINUTES));

        // Journaliser si suspect
        if ($mis < 60) {
            Log::warning("[FraudDetection] Chauffeur #{$provider->id} MIS suspect : {$mis}", [
                'stability'   => $stability,
                'velocity'    => $velocity,
                'consistency' => $consistency,
            ]);
        }

        return $mis;
    }

    /**
     * Retourne le modificateur de dispatch selon le MIS courant.
     * Utilisé par ScoreService pour pondérer le score final.
     *
     * @param  Provider $provider
     * @return float  De 0.0 (exclusion) à 1.0 (score intact)
     */
    public function getDispatchModifier(Provider $provider): float
    {
        $mis = (float) Cache::get("mis_{$provider->id}", 100.0);

        if ($mis > 80) return 1.0;   // CLEAN
        if ($mis > 60) return 1.0;   // MONITORING — pas de pénalité, juste log
        if ($mis > 30) return 0.5;   // DISPATCH_REDUCED
        return 0.0;                   // FREEZE_REVIEW
    }

    /**
     * Retourne le statut textuel du chauffeur selon son MIS.
     *
     * @param  Provider $provider
     * @return string
     */
    public function getStatus(Provider $provider): string
    {
        $mis = (float) Cache::get("mis_{$provider->id}", 100.0);

        if ($mis > 80) return 'STATUS_CLEAN';
        if ($mis > 60) return 'MONITORING';
        if ($mis > 30) return 'DISPATCH_REDUCED';
        return 'FREEZE_REVIEW';
    }

    // =========================================================================
    //  ANALYSEUR 1 : STABILITÉ DU SIGNAL GPS (0–100)
    //  Détecte le GPS jitter (instabilité du signal, émulateur, VPN)
    // =========================================================================

    private function analyzeStability(array $pings): float
    {
        // Pénalité si mock_location détecté sur au moins 1 ping
        $mockCount = collect($pings)->where('is_mock_location', true)->count();
        if ($mockCount > 0) {
            Log::warning("[FraudDetection] mock_location détecté ({$mockCount} pings)");
            return 0.0; // Fraude confirmée
        }

        // Calcul de l'écart-type des positions (jitter GPS)
        $lats = array_column($pings, 'lat');
        $lngs = array_column($pings, 'lng');
        $stdLat = $this->stdDev($lats);
        $stdLng = $this->stdDev($lngs);
        $jitter = max($stdLat, $stdLng);

        // Plus le jitter est faible, plus le signal est stable
        if ($jitter < 0.00005) return 100.0;   // Signal parfait
        if ($jitter < 0.0001)  return 90.0;
        if ($jitter < 0.0005)  return 70.0;
        if ($jitter < 0.001)   return 50.0;
        if ($jitter < 0.005)   return 20.0;
        return 0.0; // Jitter extrêmement suspect
    }

    // =========================================================================
    //  ANALYSEUR 2 : VÉLOCITÉ / COHÉRENCE DE VITESSE (0–100)
    //  Détecte téléportation et vitesses impossibles
    // =========================================================================

    private function analyzeVelocity(array $pings): float
    {
        $score = 100.0;

        for ($i = 1; $i < count($pings); $i++) {
            $prev = $pings[$i - 1];
            $curr = $pings[$i];

            $deltaTime = max(1, (int)$curr['sensor_timestamp'] - (int)$prev['sensor_timestamp']);
            $distKm    = $this->haversine(
                (float)$prev['lat'], (float)$prev['lng'],
                (float)$curr['lat'], (float)$curr['lng']
            );
            $speedKmh = ($distKm / $deltaTime) * 3600;

            // Détection téléportation
            if ($distKm > self::TELEPORT_DISTANCE_KM && $deltaTime < self::TELEPORT_TIME_SECONDS) {
                Log::warning("[FraudDetection] Téléportation détectée : {$distKm} km en {$deltaTime}s");
                return 0.0;
            }

            // Vitesse impossible en zone urbaine
            if ($speedKmh > self::URBAN_SPEED_LIMIT_KMH) {
                $score -= 30.0;
            }

            // Comparaison avec vitesse déclarée par le device (si disponible)
            $declaredSpeed = (float)($curr['speed_kmh'] ?? -1);
            if ($declaredSpeed >= 0 && abs($speedKmh - $declaredSpeed) > 50) {
                $score -= 20.0; // Discordance GPS/capteur
            }
        }

        return max(0.0, $score);
    }

    // =========================================================================
    //  ANALYSEUR 3 : COHÉRENCE DEVICE / RÉSEAU (0–100)
    //  Détecte émulateurs, multi-device, fingerprint spoofing
    // =========================================================================

    private function analyzeConsistency(array $pings, Provider $provider): float
    {
        $score = 100.0;

        // Vérification de l'unicité du device fingerprint
        $fingerprints = array_unique(array_column($pings, 'device_fingerprint_hash'));
        if (count($fingerprints) > 1) {
            // Plusieurs devices dans la même session → très suspect
            Log::warning("[FraudDetection] Multi-device fingerprint : " . implode(', ', $fingerprints));
            $score -= 50.0;
        }

        // Vérification de la cohérence de la précision GPS (accuracy_meters)
        $accuracies = array_column($pings, 'accuracy_meters');
        $avgAccuracy = count($accuracies) > 0 ? array_sum($accuracies) / count($accuracies) : 10;
        if ($avgAccuracy > 100) {
            // Précision très faible = signal réseau uniquement (pas de GPS réel)
            $score -= 30.0;
        }

        // Vérification du type réseau (si network_type est disponible)
        $networkTypes = array_unique(array_filter(array_column($pings, 'network_type')));
        if (count($networkTypes) > 2) {
            // Changements fréquents de réseau en quelques secondes
            $score -= 20.0;
        }

        return max(0.0, $score);
    }

    // =========================================================================
    //  HELPERS
    // =========================================================================

    /**
     * Distance Haversine entre 2 points GPS (en km).
     */
    private function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $r    = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a    = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return $r * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    /**
     * Écart-type d'un tableau de valeurs numériques.
     */
    private function stdDev(array $values): float
    {
        if (count($values) < 2) return 0.0;
        $mean = array_sum($values) / count($values);
        $sq   = array_map(fn($v) => ($v - $mean) ** 2, $values);
        return sqrt(array_sum($sq) / count($sq));
    }
}
