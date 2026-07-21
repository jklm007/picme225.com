<?php

namespace App\Services\DispatchEngine;

use App\Models\Provider;
use App\Models\ProviderService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * MatchingService - Orchestrateur du Moteur de Dispatch Intelligent
 *
 * Rôle : Remplacer progressivement la logique de dispatch brute de
 * UserApiController::send_request() par un pipeline intelligent en 4 étapes.
 *
 * Pipeline de Matching :
 * =========================================================
 * ÉTAPE 1  →  Bounding Box SQL (GeoService) → ~50 candidats
 * ÉTAPE 2  →  Filtres durs (statut, service, Smart Mode)  → ~20 candidats
 * ÉTAPE 3  →  Scoring IA (ScoreService)                  → score 0-100
 * ÉTAPE 4  →  Tri + Sélection des 10 meilleurs           → liste finale
 * =========================================================
 *
 * Feature Flag :
 * Ce service est activé uniquement si le paramètre 'use_ai_dispatch' = true
 * dans les Settings admin. Sinon, le vieux dispatch prend le relai (fallback).
 */
class MatchingService
{
    /** @var GeoService */
    protected $geo;

    /** @var ScoreService */
    protected $score;

    /** @var RoutingService */
    protected $routing;

    /** Nombre max de chauffeurs dans le pool initial (Bounding Box) */
    const POOL_SIZE_INITIAL = 50;

    /** Nombre de chauffeurs finaux à dispatcher */
    const POOL_SIZE_FINAL = 10;

    public function __construct(GeoService $geo, ScoreService $score, RoutingService $routing)
    {
        $this->geo     = $geo;
        $this->score   = $score;
        $this->routing = $routing;
    }

    /**
     * Point d'entrée principal : trouve les meilleurs chauffeurs pour une course.
     *
     * @param  array $tripContext  Contexte complet de la course (lat, lng, service, prix...)
     * @return Collection          Collection de Providers triés par score décroissant
     */
    public function findBestDrivers(array $tripContext): Collection
    {
        $startTime = microtime(true);

        Log::info('[MatchingService] Démarrage dispatch IA', [
            'pickup'  => $tripContext['s_lat'] . ',' . $tripContext['s_lng'],
            'service' => $tripContext['service_type_id'] ?? 'N/A',
        ]);

        // == ÉTAPE 1 : Bounding Box SQL ==
        $candidates = $this->_step1_boundingBox($tripContext);

        Log::info('[MatchingService] Étape 1 (Bounding Box) → ' . $candidates->count() . ' candidats');

        if ($candidates->isEmpty()) {
            Log::warning('[MatchingService] Aucun candidat trouvé dans le rayon.');
            return collect();
        }

        // == ÉTAPE 2 : Filtres durs ==
        $candidates = $this->_step2_hardFilters($candidates, $tripContext);

        Log::info('[MatchingService] Étape 2 (Filtres durs) → ' . $candidates->count() . ' candidats');

        if ($candidates->isEmpty()) {
            return collect();
        }

        // == ÉTAPE 3 : Scoring IA ==
        $candidates = $this->_step3_scoring($candidates, $tripContext);

        // == ÉTAPE 4 : Sélection finale ==
        $finalDrivers = $candidates
            ->sortByDesc('_dispatch_score')
            ->take(self::POOL_SIZE_FINAL);

        $elapsedMs = round((microtime(true) - $startTime) * 1000, 2);

        Log::info('[MatchingService] Dispatch IA terminé en ' . $elapsedMs . 'ms → ' . $finalDrivers->count() . ' chauffeurs sélectionnés');

        return $finalDrivers->values();
    }

    // =========================================================================
    // ÉTAPE 1 : Bounding Box SQL (Filtre géographique rapide)
    // =========================================================================

    /**
     * Requête SQL ultra-optimisée avec Bounding Box + index lat/lng.
     * Réduit le scan de 10 000 à ~50 lignes maximum.
     */
    private function _step1_boundingBox(array $ctx): Collection
    {
        $lat      = (float) $ctx['s_lat'];
        $lng      = (float) $ctx['s_lng'];
        $radius   = (float) ($ctx['search_radius_km'] ?? 10.0);

        // Calculer le carré de délimitation
        $box = $this->geo->getBoundingBox($lat, $lng, $radius);

        // 1. Chauffeurs LIBRES (dont la position actuelle est dans la zone)
        $freeProviders = Provider::with(['service', 'subscriptionPlan'])
            ->select(
                'id', 'first_name', 'last_name', 'latitude', 'longitude',
                'status', 'rating', 'eco_wallet_balance', 'acceptance_rate',
                'dispatch_score', 'is_smart_mode', 'smart_mode_type',
                'smart_dest_lat', 'smart_dest_lng', 'smart_zone_radius',
                'smart_communes', 'commune', 'subscription_plan_id',
                'subscription_expires_at',
                'opt_private_ride', 'opt_share_ride', 'opt_arret_ride', 'opt_multi_stop',
                DB::raw("
                    (6371 * acos(
                        cos(radians({$lat})) * cos(radians(latitude))
                        * cos(radians(longitude) - radians({$lng}))
                        + sin(radians({$lat})) * sin(radians(latitude))
                    )) AS _distance_km
                ")
            )
            ->where('status', 'approved')
            ->whereBetween('latitude', [$box['min_lat'], $box['max_lat']])
            ->whereBetween('longitude', [$box['min_lng'], $box['max_lng']])
            ->havingRaw("_distance_km <= {$radius}")
            ->orderBy(DB::raw('_distance_km'))
            ->limit(self::POOL_SIZE_INITIAL)
            ->get()
            ->each(function ($p) {
                $p->_dispatch_type = 'FREE'; // Chauffeur libre, aucune pénalité
            });

        // 2. Chauffeurs OCCUPÉS / EN ROUTE
        // On sépare le "Pooling" (immédiat) du "Chained" (futur)
        $busyProviders = Provider::with(['service', 'subscriptionPlan'])
            ->join('user_requests', function ($join) {
                $join->on('providers.id', '=', 'user_requests.provider_id')
                     ->whereIn('user_requests.status', ['PICKEDUP', 'STARTED', 'ARRIVED']);
            })
            ->select(
                'providers.id', 'providers.first_name', 'providers.last_name', 
                'providers.latitude as current_latitude', 'providers.longitude as current_longitude',
                'user_requests.d_latitude as dropoff_latitude', 'user_requests.d_longitude as dropoff_longitude',
                'providers.status', 'providers.rating', 'providers.eco_wallet_balance', 'providers.acceptance_rate',
                'providers.dispatch_score', 'providers.is_smart_mode', 'providers.smart_mode_type',
                'providers.smart_dest_lat', 'providers.smart_dest_lng', 'providers.smart_zone_radius',
                'providers.smart_communes', 'providers.commune', 'providers.subscription_plan_id',
                'providers.subscription_expires_at',
                'providers.opt_private_ride', 'providers.opt_share_ride',
                'providers.opt_arret_ride', 'providers.opt_multi_stop',
                'user_requests.ride_variant as active_trip_variant',
                'user_requests.total_capacity',
                'user_requests.seats_booked',
                'user_requests.id as _active_trip_id'
            )
            ->where('providers.status', 'approved')
            ->get();

        $filteredBusyProviders = collect();
        $targetVariant = $ctx['ride_variant'] ?? 'prive';
        $targetIsPrivate = ($targetVariant === 'prive');
        $targetIsShared  = in_array($targetVariant, ['dynamique', 'arret']);

        foreach ($busyProviders as $p) {
            $activeVariant     = strtolower($p->active_trip_variant ?? 'prive');
            $activeIsShared    = in_array($activeVariant, ['dynamique', 'arret']);
            $hasSeatsAvailable = (int)($p->total_capacity ?? 1) > (int)($p->seats_booked ?? 0);

            // -- Règle 1 : POOLING (Covoiturage immédiat) --
            // Conditions : course active=partagée ET places dispo ET nouvelle demande=partagée
            $isPoolingEligible = $activeIsShared && $hasSeatsAvailable && $targetIsShared;

            // -- Règle 2 : CHAINED (Enchaîné, séquentiel) --
            // Conditions : nouvelle demande=privée ET le chauffeur va finir (sa course peut être n'importe quelle variante)
            $isChainedEligible = $targetIsPrivate;

            if ($isPoolingEligible) {
                // POOLING : Disponible maintenant, on utilise sa position GPS actuelle
                $dist = $this->geo->haversineDistance($lat, $lng, $p->current_latitude, $p->current_longitude);
                if ($dist <= $radius) {
                    $p->latitude       = $p->current_latitude;
                    $p->longitude      = $p->current_longitude;
                    $p->_distance_km   = $dist;
                    $p->_dispatch_type = 'POOLING';
                    $filteredBusyProviders->push($p);
                }
            } elseif ($isChainedEligible) {
                // CHAINED DISPATCH : Disponible après sa dépose, on utilise sa destination
                if ($p->dropoff_latitude && $p->dropoff_longitude) {
                    $dist = $this->geo->haversineDistance($lat, $lng, $p->dropoff_latitude, $p->dropoff_longitude);
                    if ($dist <= $radius) {
                        $p->latitude          = $p->dropoff_latitude;
                        $p->longitude         = $p->dropoff_longitude;
                        $p->_distance_km      = $dist;
                        $p->_chained_trip_id  = $p->_active_trip_id;
                        $p->_dispatch_type    = 'CHAINED';
                        $filteredBusyProviders->push($p);
                    }
                }
            }
            // Sinon : chauffeur occupé mais non éligible (ex: privé en cours pour demande partagée) → ignoré
        }

        // Fusionner et dédupliquer par ID chauffeur
        $providers = $freeProviders->concat($filteredBusyProviders)->unique('id')->values();

        return $providers;
    }

    // =========================================================================
    // ÉTAPE 2 : Filtres Durs (Règles métier non négociables)
    // =========================================================================

    private function _step2_hardFilters(Collection $providers, array $ctx): Collection
    {
        $serviceTypeId     = $ctx['service_type_id'] ?? null;
        $estimatedPrice    = (float) ($ctx['estimated_price'] ?? 0);
        $commissionRate    = (float) ($ctx['commission_rate'] ?? 15);
        $destLat           = $ctx['d_lat'] ?? null;
        $destLng           = $ctx['d_lng'] ?? null;
        $destCommune       = $ctx['d_commune'] ?? '';
        $pickupLat         = $ctx['s_lat'];
        $pickupLng         = $ctx['s_lng'];
        $withDriver        = isset($ctx['with_driver']) ? (bool) $ctx['with_driver'] : null;

        // =========================================================
        // 🎯 FILTRAGE PAR VARIANTE DE COURSE + ABONNEMENT ACTIF
        // =========================================================
        $targetVariant = strtolower($ctx['ride_variant'] ?? 'prive');
        $isCommunal    = $ctx['is_communal'] ?? false;
        $isVoyage      = $ctx['is_voyage'] ?? false;

        $variantRules = [
            'prive'         => ['column' => 'opt_private_ride', 'premium' => false],
            'partage'       => ['column' => 'opt_share_ride',   'premium' => true],
            'arret_pdp'     => ['column' => 'opt_arret_ride',   'premium' => true],
            'arret_hybride' => ['column' => 'opt_arret_ride',   'premium' => true],
            'multi_stop'    => ['column' => 'opt_multi_stop',   'premium' => false],
        ];

        if ($isCommunal) {
            $variantRules['arret_pdp']['premium'] = false;
            $variantRules['arret_hybride']['premium'] = false;
            $variantRules['partage']['premium'] = false;
        } elseif ($isVoyage) {
            $variantRules['prive']['premium'] = true;
            $variantRules['arret_pdp']['premium'] = true;
        }

        $now = \Carbon\Carbon::now();

        return $providers->filter(function (Provider $p) use (
            $serviceTypeId, $estimatedPrice, $commissionRate,
            $destLat, $destLng, $destCommune, $pickupLat, $pickupLng,
            $withDriver, $targetVariant, $variantRules, $now
        ) {
            // --- Filtre A : Service actif et bon type de véhicule ---
            if ($p->service === null) return false;
            if (!in_array($p->service->status, ['active', 'riding'])) return false;
            
            // Si le chauffeur est en 'riding', il DOIT être en mode POOLING ou CHAINED
            if ($p->service->status === 'riding' && !isset($p->_chained_trip_id) && ($p->_dispatch_type ?? '') !== 'POOLING') {
                return false;
            }

            if ($serviceTypeId && $p->service->service_type_id != $serviceTypeId) return false;

            // --- Filtre B : Solvabilité (peut payer la commission) ---
            $commission = ($estimatedPrice * $commissionRate) / 100;
            $commissionEco = $commission / 1000.0;
            if ($p->eco_wallet_balance < $commissionEco) return false;

            // --- Filtre C : Smart Mode (filtres comportementaux) ---
            if ($p->is_smart_mode) {
                $pass = $this->_applySmartModeFilter(
                    $p, $destLat, $destLng, $destCommune, $pickupLat, $pickupLng, $isCommunal
                );
                if (!$pass) return false;
            }

            // --- Filtre D : Préférence de location (Avec/Sans Chauffeur) ---
            if ($withDriver !== null) {
                $pref = $p->service->rental_driver_preference ?? 'WITH_DRIVER';
                if ($withDriver === true && $pref === 'WITHOUT_DRIVER') {
                    return false;
                }
                if ($withDriver === false && $pref === 'WITH_DRIVER') {
                    return false;
                }
            }

            // --- Filtre E : Variante de course activée par le chauffeur ---
            // Le chauffeur doit avoir coché la variante demandée dans ses paramètres
            if (isset($variantRules[$targetVariant])) {
                $rule = $variantRules[$targetVariant];
                $variantColumn = $rule['column'];
                
                if (!$p->{$variantColumn}) {
                    Log::debug("[MatchingService] Chauffeur #{$p->id} exclu : variante '{$variantColumn}' non activée.");
                    return false;
                }

                // --- Filtre F : Abonnement actif pour variantes premium ---
                if ($rule['premium']) {
                    $expires = $p->subscription_expires_at;
                    if (!$expires || \Carbon\Carbon::parse($expires)->lte($now)) {
                        Log::debug("[MatchingService] Chauffeur #{$p->id} exclu : abonnement expiré ou absent pour variante premium.");
                        return false;
                    }
                }
            }

            return true;
        });
    }

    /**
     * Applique les filtres Smart Mode (HOME, ZONE, COMMUNE, STATION).
     * Centralisé ici pour éviter la duplication avec UserApiController.
     */
    private function _applySmartModeFilter(
        Provider $p,
        ?float $destLat, ?float $destLng,
        string $destCommune,
        float $pickupLat, float $pickupLng,
        bool $isCommunal = false
    ): bool {
        // --- Règle d'Or Abidjanaise : L'étanchéité Communale ---
        // Si le service est restreint à une commune (Woro-woro, Tricycles), le chauffeur 
        // ne doit JAMAIS recevoir de courses sortant de sa commune d'affectation, 
        // peu importe le mode (HOME, ZONE, etc.).
        if ($isCommunal && $p->commune && $destCommune) {
            if (strtolower(trim($p->commune)) !== strtolower(trim($destCommune))) {
                return false;
            }
        }

        switch ($p->smart_mode_type) {

            case 'HOME':
                if (!$p->smart_dest_lat || !$destLat) return true;
                $radius = $p->smart_zone_radius ?? 5;
                $dist   = $this->geo->haversineDistance(
                    $p->smart_dest_lat, $p->smart_dest_lng,
                    $destLat, $destLng
                );
                return $dist <= ($radius * 2);

            case 'ZONE':
                $radius = $p->smart_zone_radius ?? 5;
                $dist   = $this->geo->haversineDistance(
                    $p->latitude, $p->longitude,
                    $pickupLat, $pickupLng
                );
                
                return $dist <= $radius;

            case 'COMMUNE':
                $communesJson = $p->smart_communes ?? '[]';
                $communesData = json_decode($communesJson, true);
                if (empty($communesData) || !$destCommune) return true;
                
                // Si c'est l'ancien format (array simple: ["Cocody", "Abobo"])
                if (array_keys($communesData) === range(0, count($communesData) - 1)) {
                    return in_array($destCommune, $communesData);
                }

                // Nouveau format JSON (objet: {"Cocody":"FAVORITE", "Yopougon":"BLOCKED"})
                $level = $communesData[$destCommune] ?? 'NORMAL';
                if ($level === 'BLOCKED') {
                    return false; // Bloque formellement la course
                }
                // Si ce n'est pas bloqué (FAVORITE, NORMAL, AVOID), on laisse passer pour que le ScoreService donne bonus/malus
                return true;

            case 'STATION':
                if (!$p->smart_dest_lat) return true;
                // 1. Filtrage ultra-rapide par distance (rayon élargi à 8km pour laisser la chance au routage autoroutier)
                $dist = $this->geo->haversineDistance(
                    $p->smart_dest_lat, $p->smart_dest_lng,
                    $pickupLat, $pickupLng
                );
                if ($dist > 8.0) return false;

                // 2. Filtrage intelligent par ETA (Temps de trajet < 15 minutes)
                $route = $this->routing->getRouteEstimate(
                    $p->smart_dest_lat, $p->smart_dest_lng,
                    $pickupLat, $pickupLng
                );

                if ($route) {
                    return $route['duration_min'] <= 15.0;
                }
                
                // Fallback de sécurité si l'API de routage échoue
                return $dist <= 3.0;

            default:
                // WORO_FREE, WORO_FIXED → pas de filtre géo supplémentaire
                return true;
        }
    }

    // =========================================================================
    // ÉTAPE 3 : Scoring IA (Ajout du score sur chaque chauffeur)
    // =========================================================================

    private function _step3_scoring(Collection $providers, array $ctx): Collection
    {
        return $providers->map(function (Provider $p) use ($ctx) {
            // Injecter la distance déjà calculée dans le contexte
            $ctx['driver_distance_km'] = $p->_distance_km ?? 0;

            // Calculer le score IA (0-100)
            $p->_dispatch_score = $this->score->calculate($p, $ctx);

            return $p;
        });
    }
}
