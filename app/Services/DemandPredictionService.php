<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * DemandPredictionService — PicMe V2.3
 *
 * Moteur de prédiction de la demande basé sur l'historique des courses.
 * Génère une Heatmap H(zone, heure, jour) pour anticiper les pics de demande
 * et aider les chauffeurs à se positionner stratégiquement.
 *
 * Formule :
 *   PDS = α × H(zone, heure) + β × TimePattern + γ × EventProximity
 *
 *   α = 0.50 (poids historique géographique)
 *   β = 0.30 (poids créneau horaire)
 *   γ = 0.20 (poids événementiel)
 *
 * La heatmap est pré-calculée chaque nuit par un Job Artisan (cron)
 * et stockée dans Redis avec TTL de 1h pour un accès < 10ms.
 */
class DemandPredictionService
{
    const ALPHA = 0.50; // Poids de l'historique géographique
    const BETA  = 0.30; // Poids du créneau horaire
    const GAMMA = 0.20; // Poids des événements locaux
    const CACHE_TTL_SECONDS = 3600; // 1 heure

    /**
     * Retourne le PredictedDemandScore (0–100) pour une zone et un moment donné.
     * Utilisé par ScoreService pour la composante S du dispatch V2.3.
     *
     * @param  string $geohash     Zone GeoHash (4 chars)
     * @param  int    $hourOfDay   Heure actuelle (0–23)
     * @param  int    $dayOfWeek   Jour de la semaine (1=Lun, 7=Dim)
     * @return float  Score de 0 à 100
     */
    public function getPredictedDemandScore(string $geohash, int $hourOfDay, int $dayOfWeek): float
    {
        $cacheKey = "demand_{$geohash}_{$hourOfDay}_{$dayOfWeek}";

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($geohash, $hourOfDay, $dayOfWeek) {
            return $this->computePDS($geohash, $hourOfDay, $dayOfWeek);
        });
    }

    /**
     * Calcule le PDS en interrogeant la DB (appelé uniquement si cache expiré).
     * Utilise 30 jours d'historique pour une prédiction fiable.
     */
    public function computePDS(string $geohash, int $hourOfDay, int $dayOfWeek): float
    {
        // ─── Composante H : Historique géographique ───────────────────────
        $H = $this->computeGeoHeatmap($geohash, $hourOfDay, $dayOfWeek);

        // ─── Composante T : Pattern temporel (heure + jour) ───────────────
        $T = $this->computeTimePattern($hourOfDay, $dayOfWeek);

        // ─── Composante E : Événements locaux ────────────────────────────
        $E = $this->computeEventScore($geohash);

        // Formule finale
        $pds = (self::ALPHA * $H) + (self::BETA * $T) + (self::GAMMA * $E);
        $pds = round(min(100.0, max(0.0, $pds)), 2);

        Log::debug("[DemandPrediction] Zone={$geohash} H={$H} T={$T} E={$E} → PDS={$pds}");

        return $pds;
    }

    /**
     * Pré-calcule et met en cache la heatmap complète pour toutes les zones actives.
     * Doit être appelé via un Job Artisan planifié (schedule:run, chaque nuit à 2h).
     *
     * @return int  Nombre de zones mises à jour
     */
    public function precalculateHeatmap(): int
    {
        Log::info('[DemandPrediction] Début du précalcul de la heatmap...');

        // Récupérer toutes les zones actives du dernier mois
        $zones = DB::table('user_requests')
            ->selectRaw('s_geohash as geohash')
            ->where('created_at', '>=', now()->subDays(30))
            ->whereNotNull('s_geohash')
            ->distinct()
            ->pluck('geohash');

        $count = 0;
        foreach ($zones as $zone) {
            $geohash4 = substr($zone, 0, 4);
            for ($h = 0; $h < 24; $h++) {
                for ($d = 1; $d <= 7; $d++) {
                    $pds      = $this->computePDS($geohash4, $h, $d);
                    $cacheKey = "demand_{$geohash4}_{$h}_{$d}";
                    Cache::put($cacheKey, $pds, self::CACHE_TTL_SECONDS);
                    $count++;
                }
            }
        }

        Log::info("[DemandPrediction] Heatmap précalculée : {$count} entrées mises en cache.");
        return $count;
    }

    // =========================================================================
    //  COMPOSANTES INTERNES
    // =========================================================================

    /**
     * Calcule la densité historique pour une zone + créneau horaire (0–100).
     */
    private function computeGeoHeatmap(string $geohash, int $hourOfDay, int $dayOfWeek): float
    {
        // Comptage des courses dans cette zone pour ce créneau sur 30 jours
        $count = DB::table('user_requests')
            ->where('created_at', '>=', now()->subDays(30))
            ->where('s_geohash', 'like', $geohash . '%')
            ->whereRaw('HOUR(created_at) = ?', [$hourOfDay])
            ->whereRaw('DAYOFWEEK(created_at) = ?', [$dayOfWeek])
            ->count();

        // Maximum historique global pour normalisation (toujours > 0)
        $maxCount = DB::table('user_requests')
            ->where('created_at', '>=', now()->subDays(30))
            ->whereRaw('HOUR(created_at) = ?', [$hourOfDay])
            ->whereRaw('DAYOFWEEK(created_at) = ?', [$dayOfWeek])
            ->count();

        if ($maxCount == 0) return 50.0; // Valeur neutre si pas d'historique

        return min(100.0, ($count / $maxCount) * 100);
    }

    /**
     * Calcule le score basé sur les patterns temporels connus (0–100).
     * Ex: Lundi 8h = heure de pointe matin → score élevé.
     */
    private function computeTimePattern(int $hourOfDay, int $dayOfWeek): float
    {
        // Définir les créneaux de forte demande (heures de pointe connues)
        $peakHours = [
            ['start' => 6, 'end' => 9,  'score' => 90],  // Pointe matin
            ['start' => 11,'end' => 14, 'score' => 70],  // Déjeuner
            ['start' => 17,'end' => 21, 'score' => 95],  // Pointe soir
            ['start' => 22,'end' => 24, 'score' => 75],  // Sortie soirée
        ];

        $timeScore = 20.0; // Score de base (heure creuse)
        foreach ($peakHours as $peak) {
            if ($hourOfDay >= $peak['start'] && $hourOfDay < $peak['end']) {
                $timeScore = (float) $peak['score'];
                break;
            }
        }

        // Pondération week-end (Vendredi=6, Samedi=7 dans DAYOFWEEK MySQL)
        $isWeekend = in_array($dayOfWeek, [6, 7]);
        if ($isWeekend) {
            $timeScore = min(100.0, $timeScore * 1.2);
        }

        return $timeScore;
    }

    /**
     * Retourne un score d'événement local (0–100) si un événement est en cours ou imminent.
     * V2.3 MVP : basé sur les événements enregistrés dans la table 'local_events' (si elle existe).
     */
    private function computeEventScore(string $geohash): float
    {
        // MVP : Vérifie si la table local_events existe pour éviter les erreurs
        if (!DB::getSchemaBuilder()->hasTable('local_events')) {
            return 0.0;
        }

        $eventCount = DB::table('local_events')
            ->where('geohash', 'like', substr($geohash, 0, 3) . '%')
            ->where('event_start', '<=', now()->addHours(2))
            ->where('event_end', '>=', now())
            ->count();

        if ($eventCount === 0) return 0.0;
        return min(100.0, $eventCount * 25.0); // Chaque événement = +25 pts, max 100
    }
}
