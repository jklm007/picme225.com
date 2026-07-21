<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use DB;
use Log;
use Auth;
use Hash;
use Storage;
use Setting;
use Exception;
use Notification;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use App\Http\Controllers\SendPushNotification;
use App\Notifications\ResetPasswordOTP;
use App\Helpers\Helper;
use App\Card;
use App\User;
use App\Provider;
use App\Promocode;
use App\ServiceType;
use App\Service;
use App\KmHourServiceTypePrice;
use App\UserRequests;
use App\RequestFilter;
use App\PromocodeUsage;
use App\WalletPassbook;
use App\PromocodePassbook;
use App\ProviderService;
use App\UserRequestRating;
use App\Hospital;
use App\KmHour;
use App\ServiceTypeRental;
use App\PdpStop;
use App\PdpRoute;
use App\PdpRouteSegment;
use App\PdpSegment;
use App\Services\SharedTripService;
use App\Services\ZoneFilterService;
use App\Http\Controllers\ProviderResources\TripController;
use Illuminate\Support\Facades\Validator;

class UserServiceController extends Controller
{
    public function getServiceTypes(Request $request)
    {
        // Récupérer le nom de la catégorie de service depuis la requête
        $serviceName = $request->input('service_name');

        // Antigravity: Robust mapping of incoming service names (both technical and translated)
        $searchName = $serviceName;
        $apiName = $searchName;
        $normalizedName = strtolower(trim($serviceName));
        
        if ($normalizedName == 'standard' || $normalizedName == 'taxi') {
            $searchName = 'Taxi';
        } elseif ($normalizedName == 'delivery' || $normalizedName == 'livraison') {
            $searchName = 'Livraison';
        } elseif ($normalizedName == 'rental' || $normalizedName == 'location') {
            $searchName = 'Location';
        } elseif ($normalizedName == 'urgence' || $normalizedName == 'ambulance' || str_contains($normalizedName, 'urgence') || str_contains($normalizedName, 'ambulance')) {
            $searchName = 'Urgence';
        } elseif ($normalizedName == 'partage' || $normalizedName == 'shared ride' || $normalizedName == 'shared_ride' || $normalizedName == 'share') {
            $searchName = 'Partage';
        } elseif ($normalizedName == 'outstation' || $normalizedName == 'voyage') {
            $searchName = 'Voyage';
        }

        // Antigravity: Normalize ride variant terminology
        // - prive        : Course exclusive (Taxi/Standard)
        // - partage      : Covoiturage dynamique (Taxi avec abonnement partage)
        // - arret_pdp    : Navette gare-à-gare sur ligne fixe (Taxi Inter-communal)
        // - arret_hybride: NOUVEAU - Prise en charge arrêt fixe + destination libre (Inter-communal uniquement)
        $variant = strtolower($request->input('ride_variant', 'prive'));
        if ($variant == 'dynamique' || $variant == 'partage') {
            $variant = 'partage';         // Covoiturage Taxi
        } elseif ($variant == 'arret_hybride') {
            $variant = 'arret_hybride';   // "Dernier kilomètre" Inter-communal
        } elseif ($variant == 'arret' || $variant == 'arret_pdp') {
            $variant = 'arret_pdp';       // Navette fixe gare-à-gare
        }

        // Pour Location et Urgence : ne pas filtrer par variante côté API.
        // Ces catégories ont leurs propres variantes (avec_chauffeur/sans_chauffeur, ambulance/depannage)
        // qui ne correspondent jamais à la valeur par défaut 'prive'.
        // Le filtre variante est géré côté app Android uniquement.
        $skipVariantFilter = in_array($searchName, ['Location', 'Urgence']);

        // Antigravity: Removed typeFilter at user request.
        // We now rely purely on the checkboxes (pivot table) to decide which category a service belongs to.
        $typeFilter = null;

        // Diagnostic logging (reduced to debug level for performance)

        // PERFORMANCE: Cache key includes all inputs that affect results (including locale)
        $locale = \App::getLocale();
        // Antigravity: Cache key inclut les coordonnées (TTL réduit à 60s car dépend du GPS)
        $cacheKey = 'service_types:' . md5($locale . '|' . $searchName . '|' . $variant . '|' . $request->input('s_latitude', '') . '|' . $request->input('s_longitude', '') . '|' . $request->input('d_latitude', '') . '|' . $request->input('d_longitude', ''));

        // Antigravity: Résolution du contexte de trajet via ZoneFilterService
        $zoneFilter = new ZoneFilterService();
        $tripContext = $zoneFilter->buildTripContext(
            $request->input('s_latitude') ? (float)$request->input('s_latitude') : null,
            $request->input('s_longitude') ? (float)$request->input('s_longitude') : null,
            $request->input('d_latitude')  ? (float)$request->input('d_latitude')  : null,
            $request->input('d_longitude') ? (float)$request->input('d_longitude') : null
        );
        $tripMode = $tripContext['trip_mode'];

        $result = Cache::remember($cacheKey, 60, function () use ($request, $searchName, $variant, $apiName, $skipVariantFilter, $typeFilter, $tripMode) {
            $service = Service::with([
                'serviceTypes' => function ($query) use ($request, $variant, $skipVariantFilter, $typeFilter, $tripMode) {
                    $query->with(['service.package']);

                    // Antigravity: Filter by service_type.type for category safety
                    if ($typeFilter) {
                        $query->where('service_types.type', $typeFilter);
                    }

                    if ($variant && !$skipVariantFilter) {
                        $query->where(function ($q) use ($variant) {
                            // Support des tableaux JSON pour PostgreSQL
                            $q->whereRaw('("service_types"."allowed_variants")::jsonb @> ?', ['"'.$variant.'"'])
                              ->orWhereRaw('"service_types"."allowed_variants"::text LIKE ?', ['%'.$variant.'%'])
                              ->orWhereNull('service_types.allowed_variants')
                              ->orWhereRaw('"service_types"."allowed_variants"::text = ?', ['[]'])
                              ->orWhereRaw('"service_types"."allowed_variants"::text = ?', ['""'])
                              ->orWhereRaw('"service_types"."allowed_variants"::text = ?', ['']);
                        });
                    }

                    // Antigravity: Filtrage géographique intelligent via zone_coverage
                    // Remplace l'ancienne logique ad-hoc (Haversine inline + is_communal)
                    // La résolution de commune est déjà faite en dehors du cache closure
                    if ($tripMode === 'different_communes') {
                        // Communes différentes → masquer COMMUNAL, garder INTERCOMMUNAL + TOUTE_ZONE
                        // Compatibilité ascendante : si zone_coverage absent, fallback sur booléens
                        $query->where(function ($q) {
                            $q->whereIn('service_types.zone_coverage', ['INTERCOMMUNAL', 'TOUTE_ZONE'])
                              // Fallback pour services sans zone_coverage encore peuplée
                              ->orWhere(function ($qFallback) {
                                  $qFallback->whereNull('service_types.zone_coverage')
                                            ->where(function ($qb) {
                                                $qb->where('service_types.is_communal', 0)
                                                   ->orWhereNull('service_types.is_communal');
                                            })
                                            ->where('service_types.is_intercommunal', 1);
                              });
                        });
                    }
                    // same_commune ou unknown → aucun filtre zone (tout afficher)
                }
            ])->where('name', $searchName)->first();

            if (!$service) return null;

            $translated = $service->serviceTypes->map(function ($st) {
                // Noms directement en français dans la BD — pas de traduction nécessaire
                return $st;
            });

            return ['service_name' => $service->name, 'service_types' => $translated];
        });

        if (!$result) {
            return response()->json(['status' => false, 'message' => 'Catégorie de service non trouvée'], 404);
        }

        // Aucun service compatible après filtrage
        if (empty($result['service_types']) || count($result['service_types']) === 0) {
            return response()->json([
                'status'       => true,
                'trip_context' => $tripContext,
                'service'      => [
                    'name'          => $result['service_name'],
                    'service_types' => [],
                ],
                'message'      => 'Aucun service disponible pour ce trajet.',
            ]);
        }

        return response()->json([
            'status'       => true,
            'trip_context' => $tripContext,
            'service'      => [
                'name'          => $result['service_name'],
                'service_types' => $result['service_types'],
            ],
            'message'      => null,
        ]);
    }

    protected function _normalizeVariant(string $raw): string
    {
        $v = strtolower(trim($raw));
        return match(true) {
            in_array($v, ['partage', 'dynamique']) => 'partage',
            $v === 'arret_hybride'                 => 'arret_hybride',
            in_array($v, ['arret', 'arret_pdp'])   => 'arret_pdp',
            default                                => 'prive',
        };
    }

    public function services()
    {
        try {
            // PERFORMANCE: Cache services for 5 minutes (called on every app startup)
            $result = Cache::remember('services:all', 300, function () {
                $services = Service::all();
                if ($services->isEmpty())
                    return null;
                return $services->map(function ($service) {
                    return [
                        'id' => $service->id,
                        'name' => $service->name,
                        'image_url' => $service->image_url,
                    ];
                });
            });

            if (!$result) {
                return response()->json(['error' => 'Aucun service trouvé'], 404);
            }

            return response()->json($result)
                ->header('Cache-Control', 'public, max-age=300');
        } catch (\Exception $e) {
            Log::error('Erreur dans la méthode services : ' . $e->getMessage());
            return response()->json(['error' => 'Erreur interne du serveur'], 500);
        }
    }

    // =========================================================================
    // Antigravity — Moteur de filtrage intelligent des services par trajet
    // =========================================================================

    /**
     * GET /api/user/services/filter
     *
     * Retourne tous les services compatibles avec le trajet utilisateur.
     *
     * Paramètres (query string) :
     *  - s_latitude  : latitude du départ
     *  - s_longitude : longitude du départ
     *  - d_latitude  : latitude de la destination
     *  - d_longitude : longitude de la destination
     *
     * Logique :
     *  1. Résout la commune de départ  (PdpStop → Photon → Nominatim)
     *  2. Résout la commune de destination (même chaîne)
     *  3. Compare les communes :
     *       - Même commune     → COMMUNAL + INTERCOMMUNAL + TOUTE_ZONE
     *       - Communes diff.   → INTERCOMMUNAL + TOUTE_ZONE seulement
     *       - Inconnu          → tout afficher
     *  4. Si aucun service compatible → "Aucun service disponible pour ce trajet."
     *
     * Réponse :
     * {
     *   "status": true,
     *   "trip_context": { start_commune, end_commune, is_same_commune, trip_mode },
     *   "services": [ { id, name, image_url, service_types: [...] } ],
     *   "message": null | "Aucun service disponible pour ce trajet."
     * }
     */
    public function getFilteredServices(Request $request)
    {
        try {
            $sLat = $request->input('s_latitude')  ? (float)$request->input('s_latitude')  : null;
            $sLng = $request->input('s_longitude') ? (float)$request->input('s_longitude') : null;
            $dLat = $request->input('d_latitude')  ? (float)$request->input('d_latitude')  : null;
            $dLng = $request->input('d_longitude') ? (float)$request->input('d_longitude') : null;

            // --- 1. Résolution du contexte de trajet ---
            $zoneFilter = new ZoneFilterService();
            $tripContext = $zoneFilter->buildTripContext($sLat, $sLng, $dLat, $dLng);
            $tripMode    = $tripContext['trip_mode']; // 'same_commune' | 'different_communes' | 'unknown'

            // --- 2. Cache key unique par trajet (TTL 60s) ---
            $locale   = \App::getLocale();
            $cacheKey = 'services_filter:' . md5(
                $locale . '|' . ($sLat ?? '') . '|' . ($sLng ?? '') . '|' .
                ($dLat ?? '') . '|' . ($dLng ?? '') . '|' . $tripMode
            );

            $filteredServices = Cache::remember($cacheKey, 60, function () use ($tripMode) {
                // Charger tous les services avec leurs types
                $allServices = Service::with([
                    'serviceTypes' => function ($q) use ($tripMode) {
                        // Filtrage intelligent selon le mode de trajet
                        if ($tripMode === 'different_communes') {
                            // Communes différentes → masquer les services COMMUNAL
                            $q->where(function ($inner) {
                                $inner->whereIn('service_types.zone_coverage', ['INTERCOMMUNAL', 'TOUTE_ZONE'])
                                      // Fallback ascendant : si zone_coverage non encore peuplé
                                      ->orWhere(function ($fallback) {
                                          $fallback->whereNull('service_types.zone_coverage')
                                                   ->where('service_types.is_communal', 0)
                                                   ->where('service_types.is_intercommunal', 1);
                                      });
                            });
                        }
                        // same_commune ou unknown → aucun filtre zone
                        $q->where('service_types.status', 1);
                    }
                ])->get();

                // Retirer les services sans aucun type compatible
                return $allServices->filter(function ($service) {
                    return $service->serviceTypes->isNotEmpty();
                })->map(function ($service) {
                    return [
                        'id'            => $service->id,
                        'name'          => $service->name,
                        'image_url'     => $service->image_url,
                        'service_types' => $service->serviceTypes->map(function ($st) {
                            return array_merge($st->toArray(), [
                                'zone_coverage' => $st->zone_coverage
                                    ?? (new ZoneFilterService())->inferZoneCoverage($st),
                            ]);
                        })->values(),
                    ];
                })->values();
            });

            // --- 3. Réponse structurée ---
            if ($filteredServices->isEmpty()) {
                return response()->json(
                    $zoneFilter->buildEmptyResponse($tripContext)
                );
            }

            return response()->json([
                'status'       => true,
                'trip_context' => $tripContext,
                'services'     => $filteredServices,
                'message'      => null,
            ]);

        } catch (\Exception $e) {
            Log::error('[ZoneFilter] Erreur getFilteredServices: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'status'  => false,
                'message' => trans('api.something_went_wrong'),
            ], 500);
        }
    }

    public function Hospital_based_location(Request $request)
    {
        if (!Auth::guard('api')->check()) {
            Log::warning('User is NOT authenticated for Hospital_based_location.');
            return response()->json([
                'status' => false,
                'hospitals' => [],
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'lat' => 'required|numeric',
            'long' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            Log::error('Validation error in Hospital_based_location: ', $validator->errors()->toArray());
            return response()->json([
                'status' => false,
                'hospitals' => [],
                'errors' => $validator->errors()->messages()
            ], 422);
        }

        try {
            $distance  = Setting::get('hospital_search_radius', Setting::get('provider_search_radius', 10));
            $latitude  = $request->input('lat');
            $longitude = $request->input('long');

            // PERFORMANCE: Cache hospital results for 3 minutes per location (rounded to ~1km grid)
            $cacheKey  = 'hospitals:' . round($latitude, 2) . ':' . round($longitude, 2) . ':' . $distance;
            $lat_deg = $distance / 111.0;
            $lng_deg = $distance / (111.0 * cos(deg2rad($latitude)));
            $min_lat = $latitude - $lat_deg;
            $max_lat = $latitude + $lat_deg;
            $min_lng = $longitude - $lng_deg;
            $max_lng = $longitude + $lng_deg;

            $hospitals = Cache::remember($cacheKey, 180, function () use ($latitude, $longitude, $distance, $min_lat, $max_lat, $min_lng, $max_lng) {
                return Hospital::selectRaw("
                    *,
                    ( 6371 * acos(
                        cos( radians(?) ) * cos( radians(latitude) ) * cos( radians(longitude) - radians(?) )
                        + sin( radians(?) ) * sin( radians(latitude) )
                    )) AS distance_calculated", [$latitude, $longitude, $latitude])
                    ->whereBetween('latitude', [$min_lat, $max_lat])
                    ->whereBetween('longitude', [$min_lng, $max_lng])
                    ->having('distance_calculated', '<=', $distance)
                    ->orderBy('distance_calculated', 'asc')
                    ->get();
            });

            return response()->json([
                'status'    => true,
                'hospitals' => $hospitals,
            ]);

        } catch (Exception $e) {
            Log::error('Error in Hospital_based_location: ' . $e->getMessage());
            return response()->json([
                'status'    => false,
                'hospitals' => [],
                'error'     => trans('api.something_went_wrong'),
            ], 500);
        }
    }

    public function nearbyHospitals(Request $request)
    {
        try {
            $hospitals = Hospital::where('is_available', true)->get();
            return response()->json([
                'status' => true,
                'data' => $hospitals
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching nearby hospitals: ' . $e->getMessage());
            return response()->json(['status' => false, 'error' => trans('api.something_went_wrong')], 500);
        }
    }

    public function regionalRoutes(Request $request)
    {
        try {
            $routes = \App\Models\RegionalRoute::where('is_active', true)->get();
            return response()->json([
                'status' => true,
                'data' => $routes
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching regional routes: ' . $e->getMessage());
            return response()->json(['status' => false, 'error' => trans('api.something_went_wrong')], 500);
        }
    }

    public function getPdpStops()
    {
        try {
            // PERFORMANCE: Cache stops for 5 minutes — they rarely change
            $stops = Cache::remember('pdp:stops:active', 300, function () {
                return PdpStop::where('is_active', true)
                    ->orderBy('commune')
                    ->orderBy('name')
                    ->get();
            });

            return response()->json([
                'status' => true,
                'data'   => $stops,
            ]);
        } catch (Exception $e) {
            Log::error('Error while fetching PDP stops: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'error'  => trans('api.something_went_wrong'),
            ], 500);
        }
    }

    public function getPdpRoutes(Request $request)
    {
        try {
            $serviceType = $request->has('service_type') ? \App\Models\ServiceType::find($request->service_type) : null;

            $query = PdpRoute::where('is_active', true);

            if ($serviceType) {
                // 1. Vérification de la variante PDP
                $allowedVariants = is_array($serviceType->allowed_variants) ? $serviceType->allowed_variants : [];
                if (!in_array('arret', $allowedVariants) && !in_array('arret_pdp', $allowedVariants) && !in_array('partage', $allowedVariants)) {
                    // Si le service ne supporte pas le mode Arrêt/PDP ou Partage, on ne retourne aucun itinéraire
                    return response()->json([
                        'status' => true,
                        'data' => []
                    ]);
                }

                // 1.5 Filtrage par compagnie de transport (si le service est lié à une compagnie spécifique)
                if ($serviceType->is_communal == 1) {
                    // Les routes communales sont publiques (pas de compagnie)
                    $query->whereNull('interurban_company_id');
                } else if (!empty($serviceType->interurban_company_id)) {
                    $query->where('interurban_company_id', $serviceType->interurban_company_id);
                } else {
                    // C'est un service sans compagnie (standard), il ne doit voir QUE les lignes publiques (sans compagnie)
                    $query->whereNull('interurban_company_id');
                }

                // 2. Filtrage Communal vs Inter-Communal vs Regional/Interurban
                $types = [];
                if ($serviceType->is_communal == 1) {
                    $types[] = 'COMMUNAL';
                } elseif ($serviceType->is_intercommunal == 1) {
                    $types[] = 'INTER_COMMUNAL';
                } elseif ($serviceType->is_interregional == 1) {
                    $types[] = 'INTERURBAN';
                    $types[] = 'REGIONAL';
                }

                if (!empty($types)) {
                    $query->whereIn('type', $types);
                } elseif (strtolower($request->category) == 'voyage' || strtolower($request->category) == 'outstation') {
                    $query->whereIn('type', ['INTERURBAN', 'REGIONAL']);
                }
            }

            $cacheKey = 'pdp_routes_' . ($request->service_type ?? 'all') . '_' . ($request->category ?? 'all');
            
            $routes = \Illuminate\Support\Facades\Cache::remember($cacheKey, 600, function () use ($query, $request, $serviceType) {
                $routes = $query->with([
                    'stops' => function ($q) {
                        $q->where('pdp_stops.is_active', true);
                    },
                    'segments' => function ($q) use ($request) {
                        $q->where('is_active', true);
                        if ($request->has('service_type')) {
                            $q->forServiceType($request->service_type);
                        }
                        $q->orderBy('order');
                    }
                ])
                ->get();

            // Map pivot data and APPLY OPTION B (Distance-based pricing)
            $pricePerSegment = ($serviceType && $serviceType->price_per_segment > 0) ? $serviceType->price_per_segment : 0;
            $kmPerSegment = ($serviceType && $serviceType->km_per_segment > 0) ? $serviceType->km_per_segment : 0;

            $routes->each(function ($route) use ($pricePerSegment, $kmPerSegment) {
                // Deduplicate segments by order to avoid double counting on bidirectional routes in the Android app
                $route->setRelation('segments', $route->segments->unique('order')->values());

                // Update segment prices dynamically based on distance (Option B)
                if ($pricePerSegment > 0) {
                    $route->segments->each(function ($segment) use ($pricePerSegment, $kmPerSegment) {
                        if ($kmPerSegment > 0) {
                            // Option B: Price based on distance
                            $units = ceil($segment->distance_km / $kmPerSegment);
                            if ($units < 1) $units = 1;
                            $segment->price = $units * $pricePerSegment;
                        } else {
                            // Option A: Fixed price per segment
                            $segment->price = $pricePerSegment;
                        }
                    });
                }

                $route->stops->each(function ($stop) {
                    if ($stop->pivot) {
                        $stop->order = $stop->pivot->order;
                        $stop->price = $stop->pivot->price;
                        unset($stop->pivot);
                    }
                });
            });

            return $routes;
        });

        return response()->json([
                'status' => true,
                'is_communal' => $serviceType ? ($serviceType->is_communal == 1) : false,
                'data' => $routes,
            ]);
        } catch (Exception $e) {
            Log::error('Error while fetching PDP routes: ' . $e->getMessage(), ['exception' => $e]);

            return response()->json([
                'status' => false,
                'error' => trans('api.something_went_wrong'),
            ], 500);
        }
    }

    public function getNearbyStops(Request $request)
    {
        $this->validate($request, [
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        try {
            $radius = Setting::get('pdp_search_radius', 5);
            $lat = $request->latitude;
            $lng = $request->longitude;

            // Formule Haversine pour les arrêts proches
            // Formule Haversine pour les arrêts proches avec Bounding Box
            $lat_deg = $radius / 111.0;
            $lng_deg = $radius / (111.0 * cos(deg2rad($lat)));
            $min_lat = $lat - $lat_deg;
            $max_lat = $lat + $lat_deg;
            $min_lng = $lng - $lng_deg;
            $max_lng = $lng + $lng_deg;

            $stops = PdpStop::selectRaw("*, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance", [$lat, $lng, $lat])
                ->where('is_active', true)
                ->whereBetween('latitude', [$min_lat, $max_lat])
                ->whereBetween('longitude', [$min_lng, $max_lng])
                ->having('distance', '<=', $radius)
                ->orderBy('distance')
                ->get();

            return response()->json([
                'status' => true,
                'data' => $stops,
            ]);
        } catch (Exception $e) {
            Log::error('Error fetching nearby stops: ' . $e->getMessage());
            return response()->json(['status' => false, 'error' => trans('api.something_went_wrong')], 500);
        }
    }

    public function validateStopLocation(Request $request)
    {
        $this->validate($request, [
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'stop_id' => 'required|exists:pdp_stops,id',
        ]);

        try {
            $stop = PdpStop::findOrFail($request->stop_id);
            $distanceMeters = Helper::haversineGreatCircleDistance(
                $request->latitude,
                $request->longitude,
                $stop->latitude,
                $stop->longitude
            );
            $distance = $distanceMeters / 1000;

            $maxDistance = Setting::get('pdp_validation_threshold', 0.5); // 500m par défaut

            return response()->json([
                'status' => true,
                'is_valid' => ($distance <= $maxDistance),
                'distance' => round($distance, 3),
                'threshold' => (float) $maxDistance
            ]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function estimated_fare(Request $request)
    {
        @set_time_limit(180);
        Log::info('Estimate Request Data', $request->all());

        $user = Auth::user();
        if (empty($request->s_latitude) || empty($request->s_longitude)) {
            if ($user && !empty($user->latitude) && !empty($user->longitude)) {
                $request->merge([
                    's_latitude' => $user->latitude,
                    's_longitude' => $user->longitude
                ]);
            }
        }

        // --- ÉTAPE 1: Validation Flexible des Paramètres ---
        $validator = Validator::make($request->all(), [
            's_latitude' => 'nullable|numeric',
            's_longitude' => 'nullable|numeric',
            'd_latitude' => 'nullable|numeric',
            'd_longitude' => 'nullable|numeric',
            'service_type' => 'required|numeric|exists:service_types,id',
            'package_id' => 'nullable|numeric|exists:km_hours,id',
            'rental_package_type' => 'nullable|string|in:day,hour_package',
            'leave_on' => 'nullable|date_format:Y-m-d H:i:s',
            'return_on' => 'nullable|date_format:Y-m-d H:i:s|after_or_equal:leave_on',
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed for estimated_fare:', $validator->errors()->toArray());
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $service_type = ServiceType::findOrFail($request->service_type);

            // --- ÉTAPE 1.5: Validation Communale / Intercommunale ---
            $s_lat = $request->s_latitude;
            $s_lng = $request->s_longitude;
            $d_lat = $request->d_latitude;
            $d_lng = $request->d_longitude;

            // PERFORMANCE: Détecter les communes avec cache Redis (TTL 10min)
            // Evite la Haversine SQL répétée pour les requêtes depuis la même zone
            $start_commune = null;
            $end_commune = null;

            if ($s_lat && $s_lng) {
                $startCacheKey = 'commune_25:' . round($s_lat, 3) . ':' . round($s_lng, 3);
                $start_commune = Cache::remember($startCacheKey, now()->addMinutes(10), function () use ($s_lat, $s_lng) {
                    $nearStart = \App\Models\PdpStop::join('communes', 'pdp_stops.commune_id', '=', 'communes.id')
                        ->whereRaw("(1.609344 * 3956 * acos( cos( radians(?) ) * cos( radians(latitude) ) * cos( radians(longitude) - radians(?) ) + sin( radians(?) ) * sin( radians(latitude) ) ) ) <= 25.0", [$s_lat, $s_lng, $s_lat])
                        ->selectRaw("communes.commune as commune_name, (1.609344 * 3956 * acos( cos( radians(?) ) * cos( radians(latitude) ) * cos( radians(longitude) - radians(?) ) + sin( radians(?) ) * sin( radians(latitude) ) ) ) as distance", [$s_lat, $s_lng, $s_lat])
                        ->orderBy('distance')->first();
                    return $nearStart ? $nearStart->commune_name : null;
                });
            }

            if ($d_lat && $d_lng) {
                $endCacheKey = 'commune_25:' . round($d_lat, 3) . ':' . round($d_lng, 3);
                $end_commune = Cache::remember($endCacheKey, now()->addMinutes(10), function () use ($d_lat, $d_lng) {
                    $nearEnd = \App\Models\PdpStop::join('communes', 'pdp_stops.commune_id', '=', 'communes.id')
                        ->whereRaw("(1.609344 * 3956 * acos( cos( radians(?) ) * cos( radians(latitude) ) * cos( radians(longitude) - radians(?) ) + sin( radians(?) ) * sin( radians(latitude) ) ) ) <= 25.0", [$d_lat, $d_lng, $d_lat])
                        ->selectRaw("communes.commune as commune_name, (1.609344 * 3956 * acos( cos( radians(?) ) * cos( radians(latitude) ) * cos( radians(longitude) - radians(?) ) + sin( radians(?) ) * sin( radians(latitude) ) ) ) as distance", [$d_lat, $d_lng, $d_lat])
                        ->orderBy('distance')->first();
                    return $nearEnd ? $nearEnd->commune_name : null;
                });
            }

            // Si le service est communal (ex: Woro-Woro local)
            // Contournement: La restriction communale s'applique au service Taxi, mais pas le detour de ligne.
            $is_taxi = stripos($service_type->name, 'taxi') !== false;

            if ($service_type->is_communal) {
                // 1. Vérifier si on sort de la commune de départ
                if ($start_commune && $end_commune && $start_commune !== $end_commune) {
                    return response()->json([
                        'error' => "Ce service est un service communal et est restreint à votre commune. Vous ne pouvez pas utiliser ce service pour sortir de votre commune.",
                        'start_commune' => $start_commune,
                        'end_commune' => $end_commune
                    ], 400);
                }

                // 2. Vérifier si le service appartient bien à cette commune (si spécifié)
                if ($service_type->commune && $start_commune && strtolower($service_type->commune) !== strtolower($start_commune)) {
                    return response()->json([
                        'error' => "Ce service est un service communal et est restreint à votre commune. Vous ne pouvez pas utiliser ce service pour sortir de votre commune.",
                        'start_commune' => $start_commune,
                        'service_commune' => $service_type->commune
                    ], 400);
                }

                // 3. Par sécurité pour un service communal, si la distance directe en ligne droite dépasse la limite autorisée, on bloque.
                if ($s_lat && $s_lng && $d_lat && $d_lng) {
                    $direct_meters = \App\Helpers\Helper::haversineGreatCircleDistance($s_lat, $s_lng, $d_lat, $d_lng);
                    $direct_distance_km = $direct_meters / 1000;
                    $communalMaxDist = $service_type->max_distance > 0 ? $service_type->max_distance : 15.0;
                    if ($direct_distance_km > $communalMaxDist) {
                        return response()->json([
                            'error' => "Ce trajet ({$direct_distance_km} km) dépasse le rayon d'action maximum autorisé pour ce service communal ({$communalMaxDist} km)."
                        ], 400);
                    }
                }
            }

            $price = $service_type->fixed;
            $kilometer = 0;
            $seconds = 0;
            $number_of_units = 1;
            $calculated_package_price = 0;

            // --- ÉTAPE 2: Logique de Prix Spécifique à la Location ---
            if ($request->filled('rental_package_type')) {
                // C'est une location
                if ($request->rental_package_type == 'hour_package') {
                    $packageId = $request->package_id ?: $request->rental_package;
                    if (!$packageId) {
                        return response()->json(['error' => 'package_id ou rental_package est requis pour le type de location horaire.'], 422);
                    }
                    $kmHourPackage = KmHour::findOrFail($packageId);
                    $priceEntry = KmHourServiceTypePrice::where('km_hour_id', $kmHourPackage->id)
                        ->where('service_type_id', $service_type->id)
                        ->first();
                    if (!$priceEntry) {
                        return response()->json(['error' => 'Prix non défini pour ce forfait et ce véhicule.'], 400);
                    }
                    $calculated_package_price = $priceEntry->price;

                    if ($request->filled('leave_on') && $request->filled('return_on') && $kmHourPackage->hour > 0) {
                        $actualDurationHours = Carbon::parse($request->return_on)->diffInHours(Carbon::parse($request->leave_on));
                        $number_of_units = max(1, (int) ceil($actualDurationHours / $kmHourPackage->hour));
                    }
                    $price += $calculated_package_price * $number_of_units;
                    $kilometer = $kmHourPackage->kilometer * $number_of_units;
                    $seconds = $kmHourPackage->hour * $number_of_units * 3600;

                } elseif ($request->rental_package_type == 'day') {
                    if ($service_type->day <= 0) {
                        return response()->json(['error' => 'Tarif journalier non défini pour ce véhicule.'], 400);
                    }
                    
                    $isSansChauffeur = ($request->input('ride_variant') === 'sans_chauffeur');
                    
                    if ($isSansChauffeur) {
                        $calculated_package_price = ($service_type->rental_amount ?? 0) + $service_type->day;
                        // Reset base price (fixed) for "Sans Chauffeur" as requested
                        $price = 0; 
                    } else {
                        $calculated_package_price = $service_type->day;
                    }

                    if ($request->filled('leave_on') && $request->filled('return_on')) {
                        $leaveDate = Carbon::parse($request->leave_on);
                        $returnDate = Carbon::parse($request->return_on);
                        $diff = $leaveDate->diff($returnDate);
                        $number_of_units = $diff->days;
                        if ($diff->h > 0 || $diff->i > 0 || $diff->s > 0 || $number_of_units == 0) {
                            $number_of_units++;
                        }
                        $number_of_units = max(1, $number_of_units);
                    }
                    $price += $calculated_package_price * $number_of_units;
                }
            } else {
                $s_lat = $request->s_latitude;
                $s_lng = $request->s_longitude;
                $d_lat = $request->d_latitude;
                $d_lng = $request->d_longitude;

                $normVariant = $this->_normalizeVariant($request->input('ride_variant', 'prive'));
                $is_arret_mode = in_array($normVariant, ['arret_pdp', 'arret_hybride']);
                $detected_mode = 'FREE';

                $startStop = null;
                $endStop = null;

                if ($is_arret_mode) {
                    // 1. Détermination de la station de départ
                    if ($request->filled('pickup_stop_id')) {
                        $startStop = \App\PdpStop::find($request->pickup_stop_id);
                    }
                    if (!$startStop && $s_lat && $s_lng) {
                        $startStop = \App\PdpStop::whereRaw("ST_DWithin(location::geography, ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography, COALESCE(rayon_validation_metre, 500))", [$s_lng, $s_lat])
                            ->orderByRaw("ST_Distance(location::geography, ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography) ASC", [$s_lng, $s_lat])
                            ->first();
                    }
                    if ($startStop) {
                        $s_lat = $startStop->latitude;
                        $s_lng = $startStop->longitude;
                    }

                    // 2. Détermination de la station d'arrivée (seulement pour arret_pdp gare-à-gare)
                    if ($normVariant === 'arret_pdp') {
                        if ($request->filled('dropoff_stop_id')) {
                            $endStop = \App\PdpStop::find($request->dropoff_stop_id);
                        }
                        if (!$endStop && $d_lat && $d_lng) {
                            $endStop = \App\PdpStop::whereRaw("ST_DWithin(location::geography, ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography, COALESCE(rayon_validation_metre, 500))", [$d_lng, $d_lat])
                                ->orderByRaw("ST_Distance(location::geography, ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography) ASC", [$d_lng, $d_lat])
                                ->first();
                        }
                        if ($endStop) {
                            $d_lat = $endStop->latitude;
                            $d_lng = $endStop->longitude;
                        }
                    }

                    // Détermination du mode détecté pour l'application
                    if ($normVariant === 'arret_pdp' && $startStop && $endStop && $startStop->id != $endStop->id) {
                        $commonLines = \DB::table('pdp_route_stops as a')
                            ->join('pdp_route_stops as b', 'a.pdp_route_id', '=', 'b.pdp_route_id')
                            ->where('a.pdp_stop_id', $startStop->id)
                            ->where('b.pdp_stop_id', $endStop->id)
                            ->first();
                        if ($commonLines) {
                            $detected_mode = 'LINE';
                        }
                    } elseif ($normVariant === 'arret_hybride' && $startStop) {
                        $detected_mode = 'HYBRID';
                    }
                }

                // Restriction hybride communal / détour maximum
                if ($normVariant === 'arret_hybride' && $startStop && $s_lat && $s_lng && $d_lat && $d_lng) {
                    $destLat = (float) $d_lat;
                    $destLng = (float) $d_lng;
                    $startRoutes = \DB::table('pdp_route_stops')->where('pdp_stop_id', $startStop->id)->get();
                    $bestLastKmDistance = null;

                    foreach ($startRoutes as $startRoute) {
                        $routeId = $startRoute->pdp_route_id;
                        $startOrder = $startRoute->order;
                        $exitStop = \App\PdpStop::join('pdp_route_stops as prs', 'prs.pdp_stop_id', '=', 'pdp_stops.id')
                            ->where('prs.pdp_route_id', $routeId)
                            ->where('prs.order', '>', $startOrder)
                            ->where('pdp_stops.is_active', true)
                            ->selectRaw("pdp_stops.*, prs.order as prs_order, (6371 * acos(cos(radians($destLat)) * cos(radians(latitude)) * cos(radians(longitude) - radians($destLng)) + sin(radians($destLat)) * sin(radians(latitude)))) AS dist_to_dest")
                            ->orderBy('dist_to_dest', 'asc')
                            ->first();

                        if ($exitStop) {
                            $exitLat = (float) $exitStop->latitude;
                            $exitLng = (float) $exitStop->longitude;
                            $lastKmDistance = 6371 * acos(cos(deg2rad($exitLat)) * cos(deg2rad($destLat)) * cos(deg2rad($destLng) - deg2rad($exitLng)) + sin(deg2rad($exitLat)) * sin(deg2rad($destLat)));
                            if ($bestLastKmDistance === null || $lastKmDistance < $bestLastKmDistance) {
                                $bestLastKmDistance = $lastKmDistance;
                            }
                        }
                    }

                    if ($bestLastKmDistance !== null) {
                        $maxDetourKm = $service_type->max_detour_communal ?? $service_type->max_detour ?? 5.0;
                        if ($service_type->is_communal && !$is_taxi && $bestLastKmDistance > $maxDetourKm) {
                            return response()->json([
                                'error' => "Le détour vers votre destination libre (".round($bestLastKmDistance, 1)." km) dépasse la limite autorisée ({$maxDetourKm} km) pour ce Service Communal. Veuillez choisir une ligne plus proche ou un service inter-urbain/privé."
                            ], 400);
                        }
                    }
                }

                // --- ÉTAPE 3B: Logique de Routage via OSRM (Calcul de Distance Classique) ---
                $waypoints = $request->input('waypoints');
                $routing = get_osrm_routing($s_lat, $s_lng, $d_lat, $d_lng, $waypoints);

                if ($routing) {
                    $meter = $routing['distance'];
                    $seconds = $routing['duration'];
                    Log::info("OSRM routing successful: {$meter}m, {$seconds}s");
                } else {
                    Log::warning('OSRM routing failed, using geometric fallback');
                    $meter = get_distance_fallback($s_lat, $s_lng, $d_lat, $d_lng, $waypoints);
                    $seconds = get_duration_estimate($meter);
                }

                $kilometer = round($meter / 1000, 2);
                $minutes = round($seconds / 60, 2);

                // Validation Géo-Zone (Rayon d'action max)
                if ($service_type->max_distance > 0 && $kilometer > $service_type->max_distance) {
                    return response()->json([
                        'error' => "Ce trajet ({$kilometer} km) dépasse le rayon d'action maximum autorisé pour ce service ({$service_type->max_distance} km)."
                    ], 400);
                }

                if ($service_type->is_communal) {
                    $communalMaxDist = $service_type->max_distance > 0 ? $service_type->max_distance : 15.0;
                    if ($kilometer > $communalMaxDist) {
                        return response()->json([
                            'error' => "Ce trajet ({$kilometer} km) dépasse le rayon d'action maximum autorisé pour ce service communal ({$communalMaxDist} km)."
                        ], 400);
                    }
                }

                // Déduction des kilomètres inclus dans le prix de base !
                $chargeable_kilometer = max(0, $kilometer - ($service_type->distance ?? 0));

                switch ($service_type->calculator) {
                    case 'MIN':
                        $price += $service_type->minute * $minutes;
                        break;
                    case 'HOUR':
                        $price += $service_type->minute * 60;
                        break;
                    case 'DISTANCE':
                        $price += $chargeable_kilometer * $service_type->price;
                        break;
                    case 'DISTANCEMIN':
                        $price += ($chargeable_kilometer * $service_type->price) + ($service_type->minute * $minutes);
                        break;
                    case 'DISTANCEHOUR':
                        $price += ($chargeable_kilometer * $service_type->price) + ($service_type->minute * $minutes * 60);
                        break;
                    default:
                        $price += $chargeable_kilometer * $service_type->price;
                        break;
                }

                // Surcharge Voyage (Outstation) & Aller-Retour
                if ($request->has('method') && $request->input('method') == 'outstation') {
                    $outstation_price_per_km = $service_type->outstation_price > 0 ? $service_type->outstation_price : $service_type->price;
                    $price = ($kilometer * $outstation_price_per_km);
                    if ($request->round_trip == 1) {
                        $price = ($price * 2);
                    }
                }

                // --- ÉTAPE 3C: Variante PARTAGÉ (Éco) et ARRÊT PDP (Solution B Harmonisée) ---
                $isPartage = strtolower($request->input('ride_variant', 'prive')) === 'partage';

                if ($isPartage || $is_arret_mode) {
                    $booked = (int) $request->input('booked', 1);
                    if ($booked < 1) $booked = 1;

                    // Remise progressive basée sur la distance
                    $dist_km = (float) ($kilometer > 0 ? $kilometer : ($request->input('distance', 0)));

                    if ($dist_km > 0) {
                        if ($dist_km < 3) {
                            $discount_pct = (float) \Setting::get('partage_discount_short', '65');
                        } elseif ($dist_km <= 10) {
                            $discount_pct = (float) \Setting::get('partage_discount_medium', '55');
                        } else {
                            $discount_pct = (float) \Setting::get('partage_discount_long', '45');
                        }
                    } else {
                        $discount_pct = (float) \Setting::get('partage_sharing_discount', '55');
                    }

                    $discount_pct = max(20.0, min(80.0, $discount_pct));
                    $partage_factor = 1.0 - ($discount_pct / 100.0);

                    // Prix de base par place (avec remise partage)
                    $price_per_seat = $price * $partage_factor;

                    // Si mode arrêt : abattement additionnel pour effort de marche (arret_walk_discount, par défaut 15%)
                    if ($is_arret_mode) {
                        $walk_discount_pct = (float) \Setting::get('arret_walk_discount', '15');
                        $walk_discount_pct = max(0.0, min(50.0, $walk_discount_pct));
                        $walk_factor = 1.0 - ($walk_discount_pct / 100.0);
                        
                        $price_per_seat = $price_per_seat * $walk_factor;
                    }

                    // Prix total = prix par place × nombre de places
                    $price = $price_per_seat * $booked;

                    // Application du tarif minimum requis
                    if ($is_arret_mode) {
                        $arret_min = (float) \Setting::get('arret_min_fare', '300');
                        $total_min_required = $arret_min * $booked;
                    } else {
                        $partage_min = (float) \Setting::get('partage_min_fare', '300');
                        $total_min_required = $partage_min * $booked;
                    }

                    if ($price < $total_min_required) {
                        $price = $total_min_required;
                    }

                    \Log::info("Unified sharing pricing: mode=" . ($is_arret_mode ? "ARRET" : "PARTAGE") . ", dist={$dist_km}km, discount={$discount_pct}%, price_per_seat={$price_per_seat}, booked={$booked}, total={$price}");
                }
            }

            // --- ÉTAPE 4: Logique Commune (Taxes, Surge, Karma & Badges) ---
            $tax_percentage = (float) Setting::get('tax_percentage', 0);
            $tax_price = ($tax_percentage / 100) * $price;
            $total = $price + $tax_price;

            // 💎 RÉDUCTION KARMA / BADGE (Quotas de trajets) - Désormais en Cashback après course
            $karmaDiscount = 0;
            $user = Auth::user();
            if ($user && $user->current_discount_rate > 0) {
                if ($user->discount_trips_remaining > 0 || $user->discount_trips_remaining == -1) {
                    $karmaDiscount = $user->current_discount_rate;
                    
                    // On ne réduit plus le total ici pour la ville (système de cashback).
                    // Mais pour le Voyage (outstation), on applique la réduction immédiatement :
                    if ($request->has('method') && $request->input('method') == 'outstation') {
                        $total = $total * (1 - $karmaDiscount);
                    }
                }
            }

            $ActiveProviders = ProviderService::AvailableServiceProvider($request->service_type)->get()->pluck('provider_id');
            $distance = Setting::get('provider_search_radius', 10);
            $latitude = $request->s_latitude;
            $longitude = $request->s_longitude;

            // PERFORMANCE: Bounding Box pré-filtre — exploite l'index composite (latitude, longitude)
            // avant d'appliquer la formule Haversine coûteuse sur tous les providers
            $lat_deg = $distance / 111.0;
            $lng_deg = $distance / (111.0 * cos(deg2rad((float)$latitude)));
            $min_lat = (float)$latitude - $lat_deg;
            $max_lat = (float)$latitude + $lat_deg;
            $min_lng = (float)$longitude - $lng_deg;
            $max_lng = (float)$longitude + $lng_deg;

            $Providers = Provider::whereIn('id', $ActiveProviders)
                ->where('status', 'approved')
                ->whereBetween('latitude', [$min_lat, $max_lat])
                ->whereBetween('longitude', [$min_lng, $max_lng])
                ->whereRaw("(1.609344 * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance")
                ->get();
            // [V2.3] Surge Engine - Multiplicateur dynamique basé sur l'offre et la demande réelle
            /** @var \App\Services\SurgeEngineService $surgeEngine */
            $surgeEngine = app(\App\Services\SurgeEngineService::class);
            /** @var \App\Services\DispatchEngine\GeoService $geoService */
            $geoService = app(\App\Services\DispatchEngine\GeoService::class);

            $geohash = $geoService->encode((float)$latitude, (float)$longitude, 4);
            $surgeData = $surgeEngine->applyToPrice($total, $geohash);

            $total = $surgeData['final_price'];
            $surge = $surgeData['is_surge'] ? 1 : 0;
            $surge_percentage = $surgeData['surge_factor'] . "X";

            // 💰 RÉDEMPTION DE POINTS KARMA (Rémunération par points)
            // Taux: 1 point = 10 FCFA. On peut payer max 50% du trajet en points.
            $maxPointsRedeemable = (int) (($total * 0.5) / 10); 
            $userCanRedeem = $user ? min($user->social_points, $maxPointsRedeemable) : 0;
            $redeemValue = $userCanRedeem * 10;

            // Calcul du séquestre logistique pour location
            $escrow_fee = 0;
            $isLocation = $service_type && (strtoupper($service_type->type) === 'RENTAL' || $service_type->services->where('name', 'Location')->count() > 0);
            if ($isLocation) {
                $escrow_fee = ($price > 100000) ? $price * 0.05 : 5000;
            }

            // --- Validation Géo-Zone finale après calcul de l'itinéraire ---
            if ($service_type->max_distance > 0 && $kilometer > $service_type->max_distance) {
                return response()->json([
                    'error' => "Ce trajet ({$kilometer} km) dépasse le rayon d'action maximum autorisé pour ce service ({$service_type->max_distance} km)."
                ], 400);
            }

            if ($service_type->is_communal) {
                $communalMaxDist = $service_type->max_distance > 0 ? $service_type->max_distance : 15.0;
                if ($kilometer > $communalMaxDist) {
                    return response()->json([
                        'error' => "Ce trajet ({$kilometer} km) dépasse le rayon d'action maximum autorisé pour ce service communal ({$communalMaxDist} km)."
                    ], 400);
                }
            }

            $response = [
                'estimated_fare' => round($total, 2),
                'distance' => $kilometer,
                'time' => gmdate("H:i:s", $seconds),
                'surge' => $surge,
                'surge_value' => $surge_percentage,
                'tax_price' => round($tax_price, 2),
                'base_price' => $service_type->fixed,
                'wallet_balance' => Auth::user()->wallet_balance ?? 0,
                'social_points' => Auth::user()->social_points ?? 0,
                'karma_discount' => $karmaDiscount * 100 . "%",
                'max_points_redeem' => $userCanRedeem,
                'points_value_fcfa' => $redeemValue,
                'method' => $request->input('method'),
                'round_trip' => $request->round_trip ?? '',
                'base_price_of_unit' => round($calculated_package_price, 2),
                'number_of_units' => $number_of_units,
                'escrow_fee' => round($escrow_fee, 2),
            ];

            if (isset($is_arret_mode) && $is_arret_mode) {
                $response['detected_mode'] = $detected_mode ?? 'FREE';
            }

            return response()->json($response);

        } catch (\Exception $e) {
            \Log::error('Error in estimated_fare', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Une erreur interne est survenue. Veuillez réessayer.'], 500);
        }
    }

    public function estimated_fare_shared(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_type_id' => 'required|exists:service_types,id',
            'segments' => 'required',
            'passenger_count' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $serviceType = ServiceType::findOrFail($request->service_type_id);
        $segments = SharedTripService::normalizeSegments($request->segments);

        if (empty($segments)) {
            return response()->json(['error' => trans('api.ride.invalid_segments')], 422);
        }

        $passengerCount = max(1, (int) $request->passenger_count);
        $fare = SharedTripService::estimateFare($serviceType, $segments, $passengerCount);
        $segmentsWithEstimates = SharedTripService::hydrateSegmentsWithEstimates($segments);

        return response()->json([
            'fare' => $fare,
            'segments' => $segmentsWithEstimates,
            'max_detour_minutes' => SharedTripService::maxDetour($serviceType),
            'max_stop_distance_km' => SharedTripService::maxStopDistanceKm(),
        ]);
    }

    public function pricing_logic($id)
    {
        //return $id;
        $logic = ServiceType::select('calculator')->where('id', $id)->first();
        return $logic;

    }

    public function rental_package(Request $request)
    {
        // Étape 1: Valider le paramètre d'entrée.
        // L'application Android envoie `rental_packageid` qui est en fait le service_type_id.
        $validator = Validator::make($request->all(), [
            'rental_packageid' => 'required|integer|exists:service_types,id'
        ]);

        if ($validator->fails()) {
            Log::warning('Validation failed for rental_package API', ['errors' => $validator->errors(), 'request' => $request->all()]);
            return response()->json(['error' => 'Paramètre service_type_id invalide ou manquant.'], 422);
        }

        $serviceTypeId = $request->query('rental_packageid');
        Log::info("API rental_package: Recherche des forfaits pour service_type_id: {$serviceTypeId}");

        try {
            // Étape 2: Construire la requête pour obtenir les forfaits et leurs prix spécifiques.
            $packagesWithPrices = KmHour::select(
                'km_hours.id',
                'km_hours.kilometer',
                'km_hours.hour',
                'km_hour_service_type_prices.price'
            )
                ->join(
                    'km_hour_service_type_prices',
                    'km_hours.id',
                    '=',
                    'km_hour_service_type_prices.km_hour_id'
                )
                ->where(
                    'km_hour_service_type_prices.service_type_id',
                    $serviceTypeId
                )
                ->where('km_hour_service_type_prices.price', '>', 0) // Ne retourner que les forfaits avec un prix défini
                ->orderBy('km_hours.hour', 'asc')
                ->get();

            Log::info("Found " . $packagesWithPrices->count() . " packages for service_type_id: {$serviceTypeId}");

            // Étape 3: Retourner les résultats en JSON.
            return response()->json($packagesWithPrices);

        } catch (Exception $e) {
            Log::error("Error in rental_package API for service_type_id {$serviceTypeId}: " . $e->getMessage());
            return response()->json(['error' => trans('api.something_went_wrong')], 500);
        }
    }

    public function estimate_rental_fare(Request $request)
    {
        \Log::info('Estimate Rental Fare Request', $request->all());

        $validator = Validator::make($request->all(), [
            's_latitude' => 'required|numeric',
            's_longitude' => 'required|numeric',
            'service_type' => 'required|numeric|exists:service_types,id',
            'package_id' => 'nullable|numeric|exists:km_hours,id',
            'rental_package_type' => 'required|string|in:day,hour_package',
            'leave_on' => 'required|date_format:Y-m-d H:i:s',
            'return_on' => 'required|date_format:Y-m-d H:i:s|after_or_equal:leave_on',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $service_type = ServiceType::findOrFail($request->service_type);
            $price = $service_type->fixed;
            $kilometer = 0;
            $seconds = 0;
            $number_of_units = 1;
            $calculated_package_price = 0;

            if ($request->rental_package_type === 'hour_package') {
                if (!$request->filled('package_id')) {
                    return response()->json(['error' => 'package_id est requis pour le type de location horaire.'], 422);
                }

                $kmHourPackage = KmHour::findOrFail($request->package_id);
                $priceEntry = KmHourServiceTypePrice::where('km_hour_id', $kmHourPackage->id)
                    ->where('service_type_id', $service_type->id)
                    ->first();

                if (!$priceEntry || $priceEntry->price <= 0) {
                    return response()->json(['error' => 'Prix non défini pour ce forfait et ce véhicule.'], 400);
                }

                $calculated_package_price = $priceEntry->price;

                $actualDurationHours = Carbon::parse($request->return_on)
                    ->diffInHours(Carbon::parse($request->leave_on));
                $number_of_units = max(1, ceil($actualDurationHours / $kmHourPackage->hour));

                $price += $calculated_package_price * $number_of_units;
                $kilometer = $kmHourPackage->kilometer * $number_of_units;
                $seconds = $kmHourPackage->hour * $number_of_units * 3600;

            } elseif ($request->rental_package_type === 'day') {
                if ($service_type->day <= 0) {
                    return response()->json(['error' => 'Tarif journalier non défini pour ce véhicule.'], 400);
                }

                $calculated_package_price = $service_type->day;

                $leaveDate = Carbon::parse($request->leave_on);
                $returnDate = Carbon::parse($request->return_on);
                $diff = $leaveDate->diff($returnDate);
                $number_of_units = $diff->days;

                if ($diff->h > 0 || $diff->i > 0 || $diff->s > 0 || $number_of_units == 0) {
                    $number_of_units++; // au moins 1 jour
                }

                $price += $calculated_package_price * $number_of_units;
            }

            $tax_percentage = (float) Setting::get('tax_percentage', 0);
            $tax_price = ($tax_percentage / 100) * $price;
            $total = $price + $tax_price;

            return response()->json([
                'estimated_fare' => round($total, 2),
                'distance' => $kilometer,
                'time' => gmdate("H:i:s", $seconds),
                'tax_price' => round($tax_price, 2),
                'base_price' => $service_type->fixed,
                'wallet_balance' => Auth::user()->wallet_balance ?? 0,
                'base_price_of_unit' => round($calculated_package_price, 2),
                'number_of_units' => $number_of_units,
            ]);
        } catch (\Exception $e) {
            \Log::error('Rental Estimate Error', ['error' => $e->getMessage()]);
            return response()->json(['error' => trans('api.something_went_wrong')], 500);
        }
    }

    public function rental_service()
    {
        try {

            $data = ServiceType::where('ambulance', '!=', '1')->with('service')->get();
            foreach ($data as $val) {
                if (count($val->service) != 0) {
                    $service[] = $val;
                }

            }
            return $service;


        } catch (Exception $e) {

            return response()->json(['error' => trans('api.something_went_wrong')], 500);
        }

    }

}