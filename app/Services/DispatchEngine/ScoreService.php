<?php

namespace App\Services\DispatchEngine;

use App\Models\Provider;

/**
 * ScoreService - Moteur de Scoring IA pour le Dispatch
 *
 * Rôle : Calculer une note de 0 à 100 pour chaque chauffeur disponible,
 * en fonction du contexte précis d'une course (position, destination, profil).
 *
 * Ce score est INTERNE et TEMPORAIRE. Il n'est jamais visible par le
 * chauffeur ou le client, et n'affecte pas la note (étoiles) du chauffeur.
 *
 * Critères et pondérations (total = 100 pts) :
 * -----------------------------------------------
 * | Critère                     | Poids |
 * |-----------------------------|-------|
 * | Distance (proximité)        |  35   |
 * | Direction / Mode HOME       |  25   |
 * | Taux d'acceptation          |  20   |
 * | Qualité (étoiles / rating)  |  15   |
 * | Solvabilité ECO Wallet      |   5   |
 * -----------------------------------------------
 *
 * Les poids sont configurables via config('dispatch.score_weights').
 */
class ScoreService
{
    /** @var GeoService */
    protected $geo;

    /** @var RoutingService */
    protected $routing;

    /** @var array Pondérations par défaut */
    protected $weights = [
        'distance'        => 35,
        'direction_home'  => 25,
        'acceptance_rate' => 20,
        'rating'          => 15,
        'eco_solvency'    => 5,
        'commune_pref'    => 20, // NOUVEAU: Poids max pour les préférences de commune
    ];

    public function __construct(GeoService $geo, RoutingService $routing)
    {
        $this->geo = $geo;
        $this->routing = $routing;

        // Charger les pondérations personnalisées depuis la config si disponible
        if (function_exists('config') && config('dispatch.score_weights')) {
            $this->weights = array_merge($this->weights, config('dispatch.score_weights'));
        }
    }

    /**
     * Calcule le score de pertinence d'un chauffeur pour une course donnée.
     *
     * ════════════════════════════════════════════════════════════
     * PICME V2.3 — FORMULE FINALE DU DISPATCH
     *
     * FinalScore = MIS_modifier × (
     *     0.40 × D  +  Distance      (proximité client)
     *     0.25 × A  +  Activity      (anti-monopole / fatigue)
     *     0.15 × R  +  Réputation    (étoiles)
     *     0.10 × P  +  Priority Log  (abonnement + fidélité)
     *     0.10 × S     Surge+Demand  (contexte temps réel)
     * )
     *
     * MIS_modifier : [0.0 - 1.0] depuis FraudDetectionService
     * ════════════════════════════════════════════════════════════
     *
     * @param  Provider $provider     Le chauffeur à évaluer
     * @param  array    $tripContext  Contexte de la course
     * @return float   Score de 0 à 100
     */
    public function calculate(Provider $provider, array $tripContext): float
    {
        // ── Composante D : Distance (40%) ──────────────────────────────────
        // 0km → 100pts | 10km+ → 0pts (décroissance linéaire)
        $distKm = $tripContext['driver_distance_km']
            ?? $this->geo->haversineDistance(
                $provider->latitude, $provider->longitude,
                $tripContext['s_lat'], $tripContext['s_lng']
            );
        $D = max(0.0, 100.0 - ($distKm * 10.0));

        // ── Composante A : Activité Récente / Fatigue (25%) ────────────────
        // Chaque course récente (1h glissante) réduit le score de 25 pts.
        // Garantit une distribution équitable : anti-monopole de zone.
        $recentTrips = (int) \Illuminate\Support\Facades\Cache::get(
            "recent_trips_{$provider->id}", 0
        );
        $A = max(0.0, 100.0 - ($recentTrips * 25.0));

        // ── Composante R : Réputation / Étoiles (15%) ──────────────────────
        $rating = (float) ($provider->rating ?? 3.0);
        $R      = ($rating / 5.0) * 100.0;

        // ── Composante P : Priority Soft Cap Log (10%) ─────────────────────
        // Plafond logarithmique : évite la domination des anciens comptes.
        // raw=0 → P=0 | raw=1000 → P≈69 | raw=10000 → P≈92 (cap à 100)
        $rawPriority = max(0, (int) ($provider->priority ?? 0));
        $P = min(100.0, 10.0 * log(1.0 + $rawPriority));

        // ── Composante S : Surge + Demand Prediction (10%) ─────────────────
        // Combine la pression de la demande locale avec la prédiction IA.
        $geohash  = $tripContext['geohash'] ?? 'xxxx';
        $hour     = (int) now()->format('H');
        $dayOfWeek = (int) now()->format('N');

        $surgeBoost  = app(\App\Services\SurgeEngineService::class)->getDispatchBoost($geohash);
        $demandScore = app(\App\Services\DemandPredictionService::class)->getPredictedDemandScore(
            $geohash, $hour, $dayOfWeek
        );
        // La composante S est la moyenne pondérée surge (60%) + demande (40%)
        $S = ($surgeBoost * 0.60) + ($demandScore * 0.40);

        // ── Score Brut V2.3 ─────────────────────────────────────────────────
        $rawScore = (0.40 * $D) + (0.25 * $A) + (0.15 * $R) + (0.10 * $P) + (0.10 * $S);

        // ── Application du Modificateur Anti-Fraude (MIS) ──────────────────
        // MIS élevé (chauffeur propre) → modificateur = 1.0 (score intact)
        // MIS faible (suspect/fraude)  → modificateur = 0.5 ou 0.0
        $misModifier = app(\App\Services\FraudDetectionService::class)->getDispatchModifier($provider);
        $finalScore  = $rawScore * $misModifier;

        return round(min(100.0, max(0.0, $finalScore)), 2);
    }

    /**
     * Score lié à la direction du trajet et au Smart Mode HOME.
     *
     * Logique :
     * - Si le chauffeur a activé le Mode HOME, on vérifie si la destination
     *   du client est dans la direction de son domicile (Corridor Directionnel).
     * - Si non, on vérifie si le trajet va dans une direction "raisonnable".
     *
     * @return float 0 à 25 pts
     */
    private function _calcDirectionScore(Provider $provider, array $tripContext): float
    {
        $maxScore = $this->weights['direction_home'];

        // Pas de destination fournie → score neutre (moitié)
        if (empty($tripContext['d_lat']) || empty($tripContext['d_lng'])) {
            return $maxScore * 0.5;
        }

        // Angle du trajet : du point de départ client vers la destination
        $tripBearing = $this->geo->bearing(
            $tripContext['s_lat'], $tripContext['s_lng'],
            $tripContext['d_lat'], $tripContext['d_lng']
        );

        // Si le Mode HOME est actif et que les coordonnées du domicile sont définies
        if ($provider->is_smart_mode
            && $provider->smart_mode_type === 'HOME'
            && $provider->smart_dest_lat
            && $provider->smart_dest_lng
        ) {
            // Angle vers le domicile du chauffeur (depuis la position actuelle)
            $homeBearing = $this->geo->bearing(
                $provider->latitude, $provider->longitude,
                $provider->smart_dest_lat, $provider->smart_dest_lng
            );

            // La destination du client est-elle dans la direction du domicile du chauffeur ?
            if ($this->geo->isSameDirection($tripBearing, $homeBearing, 60.0)) {
                
                // VÉRIFICATION HYPER-AVANCÉE (ROUTING ETA)
                // Le client va dans la bonne direction, mais est-ce que le détour en temps est acceptable ?
                $route = $this->routing->getRouteEstimate(
                    $tripContext['d_lat'], $tripContext['d_lng'],
                    $provider->smart_dest_lat, $provider->smart_dest_lng
                );

                if ($route) {
                    // Si après avoir déposé le client, il reste moins de 30 mins pour rentrer à la maison, c'est parfait !
                    if ($route['duration_min'] <= 30) {
                        return (float) $maxScore;
                    } else {
                        // C'est dans la bonne direction mais c'est encore loin (+30 min)
                        return $maxScore * 0.8;
                    }
                }

                // Fallback si pas de routage
                return (float) $maxScore;

            } elseif ($this->geo->isSameDirection($tripBearing, $homeBearing, 90.0)) {
                // ACCEPTABLE : léger détour
                return $maxScore * 0.6;
            } else {
                // MAUVAISE direction : le chauffeur s'éloigne de chez lui
                return $maxScore * 0.1;
            }
        }

        // Sans Mode HOME : score neutre (ni bonus, ni pénalité majeure)
        return $maxScore * 0.5;
    }

    /**
     * Score lié à la capacité du chauffeur à payer la commission.
     *
     * @return float 0 à 5 pts
     */
    private function _calcSolvencyScore(Provider $provider, array $tripContext): float
    {
        $maxScore     = $this->weights['eco_solvency'];
        $price        = (float) ($tripContext['estimated_price'] ?? 0);
        $commRate     = (float) ($tripContext['commission_rate'] ?? 15);
        $commission   = ($price * $commRate) / 100;
        $commissionEco = $commission / 1000.0;
        $walletBal    = (float) ($provider->eco_wallet_balance ?? 0);

        if ($commission <= 0) return $maxScore; // Course gratuite → plein score

        // Ratio = solde / commission. Si ratio >= 5, le chauffeur est très à l'aise.
        $ratio = $commissionEco > 0 ? ($walletBal / $commissionEco) : 5;
        $ratio = min($ratio, 5); // Plafonner à 5

        return ($ratio / 5.0) * $maxScore;
    }

    /**
     * Calcule le bonus/malus selon le niveau de préférence de la commune.
     */
    private function _calcCommuneScore(Provider $provider, array $tripContext): float
    {
        if ($provider->smart_mode_type !== 'COMMUNE') {
            return 0.0;
        }

        $destCommune = $tripContext['d_commune'] ?? '';
        if (!$destCommune) return 0.0;

        $communesJson = $provider->smart_communes ?? '[]';
        $communesData = json_decode($communesJson, true);
        if (empty($communesData)) return 0.0;

        // Ancien format ["Cocody"]
        if (array_keys($communesData) === range(0, count($communesData) - 1)) {
            return in_array($destCommune, $communesData) ? $this->weights['commune_pref'] : 0.0;
        }

        // Nouveau format {"Cocody":"FAVORITE"}
        $level = $communesData[$destCommune] ?? 'NORMAL';

        switch ($level) {
            case 'FAVORITE': return $this->weights['commune_pref'];        // Max bonus (+20)
            case 'AVOID':    return -15.0;                                 // Malus (-15)
            case 'NORMAL':   return $this->weights['commune_pref'] * 0.5;  // Moitié
            default:         return 0.0;
        }
    }

    /**
     * Calcule et met à jour le score "long terme" d'un chauffeur (dispatch_score).
     * À appeler depuis un Job Artisan (cron quotidien), pas pendant un dispatch.
     *
     * Ce score long terme n'est PAS la note client (étoiles). Il est purement
     * technique et visible uniquement en administration.
     *
     * @param  Provider $provider
     * @return int   Score 0-100 enregistré en base
     */
    public function updateLongTermScore(Provider $provider): int
    {
        $score = 50; // Base neutre

        // +20 : Taux d'acceptation (chauffeur réactif)
        $acceptRate = (float) ($provider->acceptance_rate ?? 100);
        $score     += ($acceptRate / 100) * 20;

        // +15 : Qualité (étoiles moyennes)
        $rating  = (float) ($provider->rating ?? 3.0);
        $score  += ($rating / 5.0) * 15;

        // +10 : Abonnement actif (plan Premium / Gold)
        if ($provider->subscriptionPlan) {
            $score += min(10, $provider->subscriptionPlan->priority ?? 0);
        }

        // -5 : Pénalité si solde ECO faible (moins de 1 ECO / 1000 CFA)
        if ($provider->eco_wallet_balance < 1.0) {
            $score -= 5;
        }

        $finalScore = (int) min(100, max(0, $score));

        $provider->dispatch_score     = $finalScore;
        $provider->score_updated_at   = now();
        $provider->save();

        return $finalScore;
    }
}
