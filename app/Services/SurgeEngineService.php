<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * SurgeEngineService — PicMe V2.3
 *
 * Calcule le Surge Factor (prix multiplicateur) en temps réel
 * en fonction du rapport Offre/Demande par zone géographique (GeoHash).
 *
 * Formule :
 *   SDR          = Demand / (Supply + 1)
 *   SurgeFactor  = 1 + ln(1 + SDR)
 *
 * Effets :
 *   - Prix client : PrixFinal = PrixBase × SurgeFactor
 *   - Bonus chauffeur : (SurgeFactor - 1) × Commission × 0.5
 *   - Dispatch : léger boost au score si SDR > 1.5
 *
 * Cap : SurgeFactor est plafonné à 3.0 maximum.
 */
class SurgeEngineService
{
    const MAX_SURGE_FACTOR  = 3.0;   // Plafond absolu du multiplicateur
    const CACHE_TTL_SECONDS = 300;   // 5 minutes
    const HIGH_SURGE_SDR    = 1.5;   // SDR minimal pour impact sur dispatch

    /**
     * Retourne le SurgeFactor pour une zone géographique donnée.
     * Utilise le cache Redis pour éviter des requêtes DB répétées.
     *
     * @param  string $geohash  Code GeoHash de la zone (4 caractères = ~45km²)
     * @return float  SurgeFactor entre 1.0 et MAX_SURGE_FACTOR
     */
    public function getSurgeFactor(string $geohash): float
    {
        return Cache::remember("surge_{$geohash}", self::CACHE_TTL_SECONDS, function () use ($geohash) {
            return $this->computeSurgeFactor($geohash);
        });
    }

    /**
     * Calcule le SurgeFactor en interrogeant la DB (appelé uniquement si cache expiré).
     */
    public function computeSurgeFactor(string $geohash): float
    {
        // Supply : chauffeurs actifs dans la zone (status=approved)
        $supply = DB::table('providers')
            ->where('status', 'approved')
            ->where('geohash', 'like', substr($geohash, 0, 4) . '%')
            ->count();

        // Demand : courses en attente d'attribution dans la zone (dernières 10 min)
        $demand = DB::table('user_requests')
            ->where('status', 'SEARCHING')
            ->where('created_at', '>=', now()->subMinutes(10))
            ->where('s_geohash', 'like', substr($geohash, 0, 4) . '%')
            ->count();

        $sdr    = $demand / ($supply + 1);
        $factor = 1.0 + log(1 + $sdr);
        $factor = round(min(self::MAX_SURGE_FACTOR, max(1.0, $factor)), 3);

        Log::info("[SurgeEngine] Zone {$geohash} → Supply={$supply}, Demand={$demand}, SDR={$sdr}, Factor={$factor}");

        return $factor;
    }

    /**
     * Calcule le prix final du client avec le Surge Factor.
     *
     * @param  float  $basePrice   Prix de base calculé par le moteur tarifaire
     * @param  string $geohash     Zone de prise en charge
     * @return array  ['final_price', 'surge_factor', 'driver_bonus']
     */
    public function applyToPrice(float $basePrice, string $geohash): array
    {
        $factor = $this->getSurgeFactor($geohash);
        $finalPrice = round($basePrice * $factor);

        // Bonus chauffeur : la moitié de la surcharge est redistribuée au chauffeur
        $surcharge    = $finalPrice - $basePrice;
        $driverBonus  = round($surcharge * 0.5);

        return [
            'final_price'   => $finalPrice,
            'surge_factor'  => $factor,
            'driver_bonus'  => $driverBonus,
            'is_surge'      => $factor > 1.05,
        ];
    }

    /**
     * Retourne le boost de dispatch (0–10 pts) si le surge est élevé.
     * Utilisé comme composante S dans la formule du ScoreService V2.3.
     *
     * @param  string $geohash
     * @return float  Boost entre 0 et 100
     */
    public function getDispatchBoost(string $geohash): float
    {
        $factor = $this->getSurgeFactor($geohash);
        $sdr    = exp($factor - 1) - 1; // Inverse de la formule pour retrouver SDR

        if ($sdr < self::HIGH_SURGE_SDR) {
            return 0.0; // Pas de surge fort → pas de boost dispatch
        }

        // Score proportionnel : SDR=1.5 → 30pts, SDR=5 → 100pts
        $boost = min(100.0, ($sdr / 5.0) * 100);
        return round($boost, 2);
    }

    /**
     * Force la mise à jour du cache pour une zone (utile après un événement local).
     *
     * @param  string $geohash
     * @return float  Nouveau SurgeFactor
     */
    public function refreshZone(string $geohash): float
    {
        Cache::forget("surge_{$geohash}");
        $factor = $this->computeSurgeFactor($geohash);
        Cache::put("surge_{$geohash}", $factor, self::CACHE_TTL_SECONDS);
        return $factor;
    }
}
