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
use App\Services\SharedTripService;
use App\Http\Controllers\ProviderResources\TripController;
use Illuminate\Support\Facades\Validator;

class UserRideController extends Controller
{
    private function _normalizeVariant($v) {
        if ($v === 'partage' || $v === 'co-voiturage' || $v === 'covoiturage') return 'partage';
        return 'prive';
    }
    public function send_request(Request $request)
    {


        $this->validate($request, [
            's_latitude' => 'nullable|numeric',
            'd_latitude' => 'nullable|numeric',
            's_longitude' => 'nullable|numeric',
            'd_longitude' => 'nullable|numeric',
            'service_type' => 'required|numeric|exists:service_types,id',
            'promo_code' => 'exists:promocodes,promo_code',
            'distance' => 'nullable|numeric',
            'use_wallet' => 'numeric',
            'use_karma' => 'numeric|in:0,1',
            'karma_points' => 'numeric|min:0',
            'payment_mode' => 'required|in:CASH,CARD,PAYPAL',
            'card_id' => ['required_if:payment_mode,CARD', 'exists:cards,card_id,user_id,' . Auth::user()->id],
            'with_driver' => 'nullable|in:0,1,true,false',
        ]);

        $user = Auth::user();

        // Récupération automatique de la géolocalisation de l'utilisateur si le départ n'est pas défini
        if (empty($request->s_latitude) || empty($request->s_longitude)) {
            if (!empty($user->latitude) && !empty($user->longitude)) {
                $address = $request->s_address;
                if (empty($address) || strtolower($address) == 'ma position actuelle' || strtolower($address) == 'my current position') {
                    $googleMapsService = new \App\Service\GoogleMapsService();
                    $exactAddress = $googleMapsService->getFullAddress($user->latitude, $user->longitude);
                    $address = $exactAddress ?: "Ma position actuelle";
                }
                
                $request->merge([
                    's_latitude' => $user->latitude,
                    's_longitude' => $user->longitude,
                    's_address' => $address
                ]);
            } else {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['error' => 'Veuillez définir un lieu de prise en charge ou activer votre géolocalisation.'], 500);
                } else {
                    return back()->with('flash_error', 'Veuillez définir un lieu de prise en charge.');
                }
            }
        }

        $service_type_id = $request->service_type;
        $serviceTypeModel = ServiceType::with('services')->find($service_type_id);

        $isLocation = $serviceTypeModel && (strtoupper($serviceTypeModel->type) === 'RENTAL' || $serviceTypeModel->services->where('name', 'Location')->count() > 0);
        $isEmergency = $serviceTypeModel && $serviceTypeModel->services->where('name', 'Urgence')->count() > 0;

        if (empty($request->d_latitude) || empty($request->d_longitude)) {
            if (!$isLocation && !$isEmergency) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['error' => 'Veuillez obligatoirement renseigner une adresse de destination.'], 500);
                } else {
                    return back()->with('flash_error', 'Veuillez renseigner une destination.');
                }
            }
        }

        Log::info('New Request from User: ' . Auth::user()->id);
        Log::info('Request Details:', $request->all());

        $ActiveRequests = UserRequests::PendingRequest(Auth::user()->id)->count();

        if ($ActiveRequests > 0) {
            if ($request->ajax()) {
                return response()->json(['error' => trans('api.ride.request_inprogress')], 500);
            } else {
                return redirect('dashboard')->with('flash_error', 'Already request is in progress. Try again later');
            }
        }

        if ($request->has('schedule_date') && $request->has('schedule_time')) {
            $beforeschedule_time = (new Carbon("$request->schedule_date $request->schedule_time"))->subHour(1);
            $afterschedule_time = (new Carbon("$request->schedule_date $request->schedule_time"))->addHour(1);

            $CheckScheduling = UserRequests::where('status', 'SCHEDULED')
                ->where('user_id', Auth::user()->id)
                ->whereBetween('schedule_at', [$beforeschedule_time, $afterschedule_time])
                ->count();


            if ($CheckScheduling > 0) {
                if ($request->ajax()) {
                    return response()->json(['error' => trans('api.ride.request_scheduled')], 500);
                } else {
                    return redirect('dashboard')->with('flash_error', 'Already request is Scheduled on this time.');
                }
            }

        }

        $distance = Setting::get('provider_search_radius', '10');
        
        // 🌟 AVANTAGE VIP : Augmentation du rayon de recherche (Priorité en forte affluence)
        $userPoints = Auth::user()->social_points;
        if ($userPoints >= 10000) { // Légende
            $distance += 5; // +5 KM max
        } elseif ($userPoints >= 5000) { // Ambassadeur
            $distance += 4;
        } elseif ($userPoints >= 2500) { // Membre Influent
            $distance += 3;
        } elseif ($userPoints >= 1000) { // Collaborateur
            $distance += 2;
        } elseif ($userPoints >= 500) { // Membre Actif
            $distance += 1;
        }

        $latitude = $request->s_latitude;
        $longitude = $request->s_longitude;

        $withDriver = true;
        if ($isLocation) {
            $hours = 0;
            if ($request->has('rental_hours')) {
                $hours = (int)$request->rental_hours;
            } elseif ($request->has('package_id') || $request->has('rental_package')) {
                $packageId = $request->package_id ?: $request->rental_package;
                $kmHour = \App\KmHour::find($packageId);
                if ($kmHour) {
                    $hours = (int)$kmHour->hour;
                }
            }

            if ($hours < 24) {
                $withDriver = true; // Enforced with driver if duration < 24h
                Log::info("Rental under 24h: Enforcing with_driver = true");
            } elseif ($serviceTypeModel && !$serviceTypeModel->allow_without_driver) {
                $withDriver = true; // Enforced with driver if category is exclusive with driver
                Log::info("Service type does not allow rental without driver: Enforcing with_driver = true");
            } else {
                $withDriver = filter_var($request->input('with_driver', true), FILTER_VALIDATE_BOOLEAN);
            }
        }

        // 🛡️ SÉCURITÉ : Vérification KYC pour Service de Location (Rental)
        if ($isLocation) {
            if (Auth::user()->kyc_status !== 'APPROVED') {
                return response()->json([
                    'error' => 'Vérification d\'identité requise.',
                    'message' => 'Pour utiliser le service de location, vous devez faire vérifier votre identité dans votre profil.',
                    'action' => 'KYC_REQUIRED'
                ], 403);
            }
        }

        // --- LOGIQUE FEEDER (APPROCHE INTELLIGENTE) ---
        $isFeederSearch = false;
        if ($serviceTypeModel && $serviceTypeModel->is_intercity && $serviceTypeModel->requires_feeder_ride) {
            $isFeederSearch = true;
            $distance = $serviceTypeModel->feeder_trigger_radius > 0 ? $serviceTypeModel->feeder_trigger_radius : $distance;
            Log::info("Feeder Search Triggered for Request by User " . Auth::user()->id);
        }

        // 🛡️ SÉCURITÉ : Vérification Frais de Logistique (Séquestre) pour la Location
        $escrowFee = 0;
        if ($isLocation) {
            $estimatedPrice = $serviceTypeModel->fixed + ($request->distance * $serviceTypeModel->price);
            if ($request->has('rental_hours') || $request->has('package_id') || $request->has('rental_package')) {
                $packageId = $request->package_id ?: $request->rental_package;
                if ($packageId) {
                    $priceEntry = \App\KmHourServiceTypePrice::where('km_hour_id', $packageId)
                        ->where('service_type_id', $serviceTypeModel->id)
                        ->first();
                    if ($priceEntry) {
                        $estimatedPrice = $priceEntry->price;
                    }
                }
            } elseif ($request->has('rental_days')) {
                $dailyRate = $serviceTypeModel->rental_amount ?: ($serviceTypeModel->fixed ?: $serviceTypeModel->price);
                $estimatedPrice = $dailyRate * $request->rental_days;
            }

            $escrowFee = ($estimatedPrice > 100000) ? $estimatedPrice * 0.05 : 5000;
            if (Auth::user()->wallet_balance < $escrowFee) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'error' => 'Solde insuffisant dans votre portefeuille pour couvrir les frais de logistique obligatoires (séquestre).',
                        'required' => $escrowFee,
                        'wallet_balance' => Auth::user()->wallet_balance
                    ], 402);
                } else {
                    return back()->with('flash_error', 'Solde insuffisant pour la réservation.');
                }
            }
        }

        // =====================================================================
        // MOTEUR DE DISPATCH IA (MatchingService)
        // Feature Flag : activé si 'use_ai_dispatch' = true dans les Settings.
        // Fallback automatique vers l'ancien dispatch si désactivé ou si erreur.
        // =====================================================================
        $useAiDispatch = \App\Models\FeatureFlag::isEnabled('dispatch_v2_enabled');
        $aiDispatchSuccess = false;

        if ($useAiDispatch) {
            try {
                $routingService = new \App\Services\DispatchEngine\RoutingService();
                $geoService     = new \App\Services\DispatchEngine\GeoService();
                $scoreService   = new \App\Services\DispatchEngine\ScoreService($geoService, $routingService);

                $matchingService = new \App\Services\DispatchEngine\MatchingService(
                    $geoService,
                    $scoreService,
                    $routingService
                );

                $estimatedPrice = $serviceTypeModel->fixed + ($request->distance * $serviceTypeModel->price);
                $commissionRate = $serviceTypeModel->commission_percentage ?? 15;

                $isCommunalContext = $serviceTypeModel && $serviceTypeModel->is_communal;
                $isVoyageContext = $serviceTypeModel && (in_array(strtolower($serviceTypeModel->name), ['inter-communal', 'picme 7 places', 'voyage', 'voyage partage']));

                $tripContext = [
                    's_lat'            => (float) $latitude,
                    's_lng'            => (float) $longitude,
                    'd_lat'            => (float) ($request->d_latitude ?? $latitude),
                    'd_lng'            => (float) ($request->d_longitude ?? $longitude),
                    'd_commune'        => $request->input('d_commune', ''),
                    'service_type_id'  => $service_type_id,
                    'search_radius_km' => (float) $distance,
                    'estimated_price'  => $estimatedPrice,
                    'commission_rate'  => $commissionRate,
                    'ride_variant'     => $this->_normalizeVariant($request->input('ride_variant', 'prive')),
                    'pickup_stop_id'   => $request->input('pickup_stop_id'),
                    'dropoff_stop_id'  => $request->input('dropoff_stop_id'),
                    'with_driver'      => $withDriver,
                    'is_communal'      => $isCommunalContext,
                    'is_voyage'        => $isVoyageContext,
                ];

                $aiProviders = $matchingService->findBestDrivers($tripContext);

                if ($aiProviders->isNotEmpty()) {
                    // Succès : on utilise les résultats IA
                    $Providers = $aiProviders;
                    $aiDispatchSuccess = true;
                    Log::info('[MatchingService] Dispatch IA utilisé avec succès. Chauffeurs: ' . $aiProviders->count());
                } else {
                    Log::warning('[MatchingService] Aucun résultat IA, basculement sur dispatch classique.');
                }
            } catch (\Exception $e) {
                Log::error('[MatchingService] ERREUR - Fallback dispatch classique: ' . $e->getMessage());
            }
        }

        // =====================================================================
        // DISPATCH CLASSIQUE (Fallback si IA désactivée ou en erreur)
        // =====================================================================
        if (!$aiDispatchSuccess) {

        // Bounding Box filter (Pre-filter before Haversine equation to hit composite index)
        $lat_deg = $distance / 111.0;
        $lng_deg = $distance / (111.0 * cos(deg2rad($latitude)));
        $min_lat = $latitude - $lat_deg;
        $max_lat = $latitude + $lat_deg;
        $min_lng = $longitude - $lng_deg;
        $max_lng = $longitude + $lng_deg;

        $Providers = Provider::with('service')
            ->select(DB::Raw("(6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) AS distance"), 'id', 'eco_wallet_balance', 'service_type_id', 'commune')
            ->where('status', 'approved')
            ->whereBetween('latitude', [$min_lat, $max_lat])
            ->whereBetween('longitude', [$min_lng, $max_lng])
            ->whereRaw("(6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance");

        // --- FILTRAGE COMMUNAL (APPROCHE SMART) ---
        // Si le service est marqué communal, on ne cherche que les chauffeurs de la commune de départ
        if ($serviceTypeModel && $serviceTypeModel->is_communal) {
            // Identifier la commune de départ (Arrêt PDP le plus proche dans un rayon de 5km)
            $nearStart = \App\PdpStop::whereRaw("(1.609344 * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= 5.0")
                ->selectRaw("commune, (1.609344 * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) as distance")
                ->orderBy('distance')->first();

            if ($nearStart && $nearStart->commune) {
                $startCommune = $nearStart->commune;
                $Providers->where(function ($q) use ($startCommune) {
                    $q->where('commune', $startCommune)
                        ->orWhereNull('commune');
                });
                Log::info("Communal dispatch filter (Smart) applied for $startCommune on ServiceType $service_type_id");
            }
        }

        if ($isFeederSearch) {
            // On cherche n'importe quel provider dont le ServiceType a 'can_act_as_feeder' = true
            $Providers->whereHas('serviceType', function ($q) {
                $q->where('can_act_as_feeder', true);
            });
        } else {
            // Recherche standard
            $variant = $this->_normalizeVariant($request->input('ride_variant', 'prive'));
            $Providers->whereHas('service', function ($query) use ($service_type_id, $isLocation, $withDriver, $variant) {
                if ($variant === 'partage') {
                    $query->whereIn('status', ['active', 'riding']);
                } else {
                    $query->where('status', 'active');
                }
                $query->where('service_type_id', $service_type_id);
                if ($isLocation) {
                    if ($withDriver) {
                        $query->where('rental_driver_preference', '!=', 'WITHOUT_DRIVER');
                    } else {
                        $query->where('rental_driver_preference', '!=', 'WITH_DRIVER');
                    }
                }
            });
            
            if ($variant === 'partage') {
                $Providers->whereDoesntHave('trips', function($q) {
                    $q->whereIn('status', ['SEARCHING', 'ACCEPTED', 'STARTED', 'ARRIVED', 'PICKEDUP'])
                      ->where('ride_variant', '!=', 'partage');
                });
            }
        }

        $variant = $this->_normalizeVariant($request->input('ride_variant', 'prive'));

        // =========================================================
        // 🎯 FILTRAGE PAR VARIANTE + CATÉGORIE + ABONNEMENT ACTIF
        //
        // Règles par catégorie :
        //   TAXI        : Privé (std), Partagé (premium), Arrêt PDP (premium),
        //                 Arrêt Hybride (premium), Multistop (std)
        //   LIVRAISON   : Privé (std), Multistop (std)
        //   LOCATION    : Avec chauffeur (std) / Sans chauffeur (premium)
        //                 → géré via rental_driver_preference ci-dessus
        //   URGENCE     : Privé uniquement (std)
        //   VOYAGE PART.: Partagé (premium), Arrêt PDP (premium),
        //                 Arrêt Hybride (premium)
        // =========================================================
        $now = \Carbon\Carbon::now();

        // Variante → [colonne opt_, abonnement_premium]
        $variantRules = [
            'prive'         => ['column' => 'opt_private_ride', 'premium' => false],
            'partage'       => ['column' => 'opt_share_ride',   'premium' => true],
            'arret_pdp'     => ['column' => 'opt_arret_ride',   'premium' => true],
            'arret_hybride' => ['column' => 'opt_arret_ride',   'premium' => true],
            'multi_stop'    => ['column' => 'opt_multi_stop',   'premium' => false], // Standard dans toutes catégories
        ];

        $isCommunal = $serviceTypeModel && $serviceTypeModel->is_communal;
        $isVoyage = $serviceTypeModel && (in_array(strtolower($serviceTypeModel->name), ['inter-communal', 'picme 7 places', 'voyage', 'voyage partage']));

        if ($isCommunal) {
            // Catégorie PARTAGE (Woro-Woro, etc.)
            $variantRules['arret_pdp']['premium'] = false;     // ❌ Standard
            $variantRules['arret_hybride']['premium'] = false; // ❌ Standard
            $variantRules['partage']['premium'] = false;       // ❌ Standard (par défaut pour woro-woro)
        } elseif ($isVoyage) {
            // Catégorie VOYAGE
            $variantRules['prive']['premium'] = true;          // ✅ Premium
            $variantRules['arret_pdp']['premium'] = true;      // ✅ Premium
        }

        if ($isLocation) {
            // LOCATION : filtrage Avec/Sans chauffeur déjà géré via rental_driver_preference
            // La location SANS chauffeur nécessite un abonnement actif (véhicule sans conducteur)
            if (!$withDriver) {
                $Providers->where(function ($q) use ($now) {
                    $q->where('subscription_expires_at', '>', $now)
                      ->whereNotNull('subscription_expires_at');
                });
                Log::info('[Dispatch] Location SANS chauffeur : abonnement actif requis.');
            }
        } elseif (isset($variantRules[$variant])) {
            $rule = $variantRules[$variant];

            // Filtre 1 : Le chauffeur a activé cette variante dans ses paramètres
            $Providers->where($rule['column'], 1);
            Log::info("[Dispatch] Filtre variante '{$variant}' → colonne '{$rule['column']}'");

            // Filtre 2 : Abonnement actif requis pour les variantes premium
            if ($rule['premium']) {
                $Providers->where(function ($q) use ($now) {
                    $q->where('subscription_expires_at', '>', $now)
                      ->whereNotNull('subscription_expires_at');
                });
                Log::info("[Dispatch] Filtre abonnement actif appliqué pour variante premium '{$variant}'");
            }
        }

        $Providers = $Providers->orderBy('distance', 'asc')
            ->take(50) // On prend plus large pour filtrer après
            ->get();

        // --- TOKENOMICS & SOLVABILITÉ ---
        // Estimation rapide du prix pour vérifier la commission
        $estimatedPrice = $serviceTypeModel->fixed + ($request->distance * $serviceTypeModel->price);
        
        // Logique spécifique pour la location (Rental)
        if ($request->has('rental_hours') || $request->has('package_id') || $request->has('rental_package')) {
            $packageId = $request->package_id ?: $request->rental_package;
            if ($packageId) {
                $priceEntry = \App\KmHourServiceTypePrice::where('km_hour_id', $packageId)
                    ->where('service_type_id', $serviceTypeModel->id)
                    ->first();
                if ($priceEntry) {
                    $estimatedPrice = $priceEntry->price;
                }
            }
        } elseif ($request->has('rental_days')) {
            $dailyRate = $serviceTypeModel->rental_amount ?: ($serviceTypeModel->fixed ?: $serviceTypeModel->price);
            $estimatedPrice = $dailyRate * $request->rental_days;
        }

        $commissionPercentage = $serviceTypeModel->commission_percentage ?? 15;

        // Filtre : Le chauffeur peut-il payer la commission ?
        $Providers = $Providers->filter(function ($provider) use ($estimatedPrice) {
            return $provider->canAffordCommission($estimatedPrice);
        });

        // Filtre : Capacité maximale pour la livraison partagée
        $variant = $this->_normalizeVariant($request->input('ride_variant', 'prive'));
        if ($variant === 'partage') {
            $Providers = $Providers->filter(function ($provider) use ($serviceTypeModel) {
                $activeTripsCount = UserRequests::where('provider_id', $provider->id)
                    ->whereIn('status', ['SEARCHING', 'ACCEPTED', 'STARTED', 'ARRIVED', 'PICKEDUP'])
                    ->where('ride_variant', 'partage')
                    ->count();
                $maxCapacity = $serviceTypeModel->shared_capacity ?? 3;
                return $activeTripsCount < $maxCapacity;
            });
        }

        // --- SMART MODE DISPATCH FILTER ---
        // Filtrage des chauffeurs basé sur leurs préférences Smart Mode
        // $d_latitude / $d_longitude = coordonnées de destination du client
        $dest_lat  = $request->d_latitude ?: $request->s_latitude;
        $dest_lng  = $request->d_longitude ?: $request->s_longitude;
        $dest_commune = $request->input('d_commune', '');

        $Providers = $Providers->filter(function ($provider) use ($dest_lat, $dest_lng, $dest_commune, $latitude, $longitude) {
            // Si le Smart Mode n'est pas activé, le chauffeur reçoit toutes les commandes normalement
            if (!$provider->is_smart_mode) {
                return true;
            }

            $modeType = $provider->smart_mode_type ?? 'HOME';

            // MODE HOME : accepte uniquement les courses dont la destination
            // est dans un rayon de (smart_zone_radius * 2) km du domicile du chauffeur
            if ($modeType === 'HOME') {
                $homeLat  = $provider->smart_dest_lat;
                $homeLng  = $provider->smart_dest_lng;
                $radius   = $provider->smart_zone_radius ?? 5; // km
                if (!$homeLat || !$homeLng || !$dest_lat || !$dest_lng) return true;
                $distToHome = 6371 * acos(
                    cos(deg2rad($homeLat)) * cos(deg2rad($dest_lat)) *
                    cos(deg2rad($dest_lng) - deg2rad($homeLng)) +
                    sin(deg2rad($homeLat)) * sin(deg2rad($dest_lat))
                );
                return $distToHome <= ($radius * 2);
            }

            // MODE ZONE : reste dans un rayon autour de sa position actuelle
            if ($modeType === 'ZONE') {
                $radius = $provider->smart_zone_radius ?? 5;
                if (!$dest_lat || !$dest_lng) return true;
                $distToPickup = 6371 * acos(
                    cos(deg2rad($provider->latitude)) * cos(deg2rad($latitude)) *
                    cos(deg2rad($longitude) - deg2rad($provider->longitude)) +
                    sin(deg2rad($provider->latitude)) * sin(deg2rad($latitude))
                );
                return $distToPickup <= $radius;
            }

            // MODE COMMUNE : filtre par commune de destination
            if ($modeType === 'COMMUNE') {
                $communesJson = $provider->smart_communes ?? '[]';
                $selectedCommunes = json_decode($communesJson, true) ?: [];
                if (empty($selectedCommunes)) return true;
                if (!$dest_commune) return true; // Pas de commune fournie → on laisse passer
                return in_array($dest_commune, $selectedCommunes);
            }

            // MODE STATION : accepte les courses dont le départ est proche de sa gare
            if ($modeType === 'STATION') {
                $stationLat = $provider->smart_dest_lat;
                $stationLng = $provider->smart_dest_lng;
                if (!$stationLat || !$stationLng || !$latitude || !$longitude) return true;
                $distToStation = 6371 * acos(
                    cos(deg2rad($stationLat)) * cos(deg2rad($latitude)) *
                    cos(deg2rad($longitude) - deg2rad($stationLng)) +
                    sin(deg2rad($stationLat)) * sin(deg2rad($latitude))
                );
                return $distToStation <= 3.0; // Rayon fixe 3km autour de la gare
            }

            // Modes Woro (WORO_FREE, WORO_FIXED) : pas de filtrage géo supplémentaire côté dispatch
            return true;
        });
        // --- FIN SMART MODE FILTER ---

        // Tri : Priorité à la richesse ECO, puis à la distance
        $Providers = $Providers->sortByDesc('eco_wallet_balance')->sortBy('distance');

        } // fin du bloc fallback dispatch classique (if !$aiDispatchSuccess)

        // On garde les 10 meilleurs après filtrage/tri
        $Providers = $Providers->take(10);

        // List Providers who are currently busy and add them to the filter list.

        if (count($Providers) == 0) {
            if ($request->ajax()) {
                // Push Notification to User
                return response()->json(['message' => trans('api.ride.no_providers_found')]);
            } else {
                return back()->with('flash_success', 'No Providers Found! Please try again.');
            }
        }

        try {

            $waypoints = $request->input('waypoints');
            $d_lat = $request->d_latitude ?: $request->s_latitude;
            $d_lng = $request->d_longitude ?: $request->s_longitude;
            
            // Asynchronous OSRM via job (faster API response)
            $route_key = '';

            $s_addr = $request->s_address ?: "Lieu de départ";
            
            $generic_addresses = ["ma position actuelle", "lieu de départ", "current location", "ma position", "lieu de depart"];
            if (in_array(strtolower(trim(utf8_decode($s_addr))), $generic_addresses) || in_array(strtolower(trim($s_addr)), $generic_addresses)) {
                if (!empty($request->s_latitude) && !empty($request->s_longitude)) {
                    try {
                        $url = "https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=" . $request->s_latitude . "&lon=" . $request->s_longitude;
                        $opts = [
                            "http" => [
                                "header" => "User-Agent: PicmeApp/1.0\r\n"
                            ]
                        ];
                        $context = stream_context_create($opts);
                        $response = @file_get_contents($url, false, $context);
                        if ($response) {
                            $data = json_decode($response, true);
                            if (!empty($data['display_name'])) {
                                $s_addr = $data['display_name'];
                                $request->merge(['s_address' => $s_addr]);
                            }
                        }
                    } catch (\Exception $e) { 
                        \Log::error("Nominatim Geocoding failed: " . $e->getMessage());
                    }
                }
            }

            $d_addr = $request->d_address ?: $s_addr;

            $UserRequest = new UserRequests;
            $UserRequest->booking_id = Helper::generate_booking_id();
            if ($request->round_trip != '') {
                $UserRequest->round_trip = $request->round_trip;
            }
            $otp2 = substr($UserRequest->booking_id, -2);
            $otp1 = mb_substr($s_addr, 0, 1);
            $otp3 = mb_substr($d_addr, 0, 1);
            $otp = $otp1 . $otp2 . $otp3;


            $UserRequest->user_id = Auth::user()->id;

            if ((Setting::get('manual_request', 0) == 0) && (Setting::get('broadcast_request', 0) == 0)) {
                $UserRequest->current_provider_id = $Providers[0]->id;
            } else {
                $UserRequest->current_provider_id = 0;
            }

            $UserRequest->service_type_id = $request->service_type;
            $UserRequest->rental_hours = $request->rental_hours;
            $UserRequest->payment_mode = $request->payment_mode;
            $UserRequest->with_driver = $withDriver;
            $UserRequest->rental_with_driver = $withDriver;
            if ($request->has('rental_start_date')) {
                $UserRequest->rental_start_date = $request->rental_start_date;
            }
            if ($request->has('rental_end_date')) {
                $UserRequest->rental_end_date = $request->rental_end_date;
            }
            if ($request->has('rental_days')) {
                $UserRequest->rental_days = $request->rental_days;
                $UserRequest->rental_start_date = \Carbon\Carbon::now()->format('Y-m-d');
                $UserRequest->rental_end_date = \Carbon\Carbon::now()->addDays($request->rental_days)->format('Y-m-d');
            }
            if ($request->has('hospital_id')) {
                $UserRequest->hospital_id = $request->hospital_id;
            }
            $UserRequest->ride_variant = $this->_normalizeVariant($request->input('ride_variant', 'prive'));

            if ($request->has('booked')) {
                $UserRequest->seats_booked = max(1, (int) $request->input('booked', 1));
            }

            if (in_array($UserRequest->ride_variant, ['partage'], true)) {
                $UserRequest->is_pool_dynamic = true;
            }

            if (in_array($UserRequest->ride_variant, ['arret_pdp', 'arret_hybride'], true)) {
                $UserRequest->is_pdp_route = true;
                if ($request->filled('pickup_stop_id')) {
                    $UserRequest->pickup_stop_id = (int) $request->pickup_stop_id;
                }
                if ($request->filled('dropoff_stop_id')) {
                    $UserRequest->dropoff_stop_id = (int) $request->dropoff_stop_id;
                }
            }

            $UserRequest->status = 'SEARCHING';

            $UserRequest->s_address = $s_addr;
            $UserRequest->d_address = $d_addr;

            $UserRequest->s_latitude = $request->s_latitude;
            $UserRequest->s_longitude = $request->s_longitude;
            // [V2.3] Calcul du GeoHash pour le moteur de demande prédictive et Surge
            if ($request->s_latitude && $request->s_longitude) {
                /** @var \App\Services\DispatchEngine\GeoService $geoService */
                $geoService = app(\App\Services\DispatchEngine\GeoService::class);
                $UserRequest->s_geohash = $geoService->encode((float)$request->s_latitude, (float)$request->s_longitude, 5);
            }

            // Pour la location, si pas de destination, on utilise le point de départ
            $UserRequest->d_latitude = $request->d_latitude ?: $request->s_latitude;
            $UserRequest->d_longitude = $request->d_longitude ?: $request->s_longitude;
            
            $s_lat = $request->s_latitude;
            $s_lng = $request->s_longitude;
            $d_lat = $UserRequest->d_latitude;
            $d_lng = $UserRequest->d_longitude;
            $waypoints = $request->input('waypoints');

            // Calculer la distance réelle du trajet
            $actual_distance_km = 0;
            if ($s_lat && $s_lng && $d_lat && $d_lng) {
                // Essayer d'abord d'obtenir la distance OSRM (via get_osrm_routing helper)
                $routing = get_osrm_routing($s_lat, $s_lng, $d_lat, $d_lng, $waypoints);
                if ($routing) {
                    $actual_distance_km = round($routing['distance'] / 1000, 2);
                } else {
                    // Fallback sur la distance géométrique directe
                    $direct_meters = Helper::haversineGreatCircleDistance($s_lat, $s_lng, $d_lat, $d_lng);
                    $actual_distance_km = round($direct_meters / 1000, 2);
                }
            }

            // Si le client n'a pas envoyé de distance ou a envoyé une distance incorrecte
            $client_distance = (float) ($request->distance ?: 0);
            if ($client_distance <= 0 || $client_distance < ($actual_distance_km * 0.5)) {
                $UserRequest->distance = $actual_distance_km;
            } else {
                $UserRequest->distance = $client_distance;
            }

            // --- Validation Géo-Zone (Rayon d'action max) ---
            if ($serviceTypeModel && $serviceTypeModel->max_distance > 0) {
                if ($UserRequest->distance > $serviceTypeModel->max_distance) {
                    return response()->json([
                        'error' => "Ce trajet ({$UserRequest->distance} km) dépasse le rayon d'action maximum autorisé pour ce service ({$serviceTypeModel->max_distance} km)."
                    ], 400);
                }
            }

            // --- Validation Communale pour la commande ---
            if ($serviceTypeModel && $serviceTypeModel->is_communal) {
                // 1. Détection des communes avec cache (TTL 10min) et rayon élargi à 25km
                $start_commune = null;
                $end_commune = null;

                if ($s_lat && $s_lng) {
                    $startCacheKey = 'commune_25:' . round($s_lat, 3) . ':' . round($s_lng, 3);
                    $start_commune = Cache::remember($startCacheKey, now()->addMinutes(10), function () use ($s_lat, $s_lng) {
                        $nearStart = \App\PdpStop::whereRaw("(1.609344 * 3956 * acos( cos( radians('$s_lat') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$s_lng') ) + sin( radians('$s_lat') ) * sin( radians(latitude) ) ) ) <= 25.0")
                            ->selectRaw("commune, (1.609344 * 3956 * acos( cos( radians('$s_lat') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$s_lng') ) + sin( radians('$s_lat') ) * sin( radians(latitude) ) ) ) as distance")
                            ->orderBy('distance')->first();
                        return $nearStart ? $nearStart->commune : null;
                    });
                }

                if ($d_lat && $d_lng) {
                    $endCacheKey = 'commune_25:' . round($d_lat, 3) . ':' . round($d_lng, 3);
                    $end_commune = Cache::remember($endCacheKey, now()->addMinutes(10), function () use ($d_lat, $d_lng) {
                        $nearEnd = \App\PdpStop::whereRaw("(1.609344 * 3956 * acos( cos( radians('$d_lat') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$d_lng') ) + sin( radians('$d_lat') ) * sin( radians(latitude) ) ) ) <= 25.0")
                            ->selectRaw("commune, (1.609344 * 3956 * acos( cos( radians('$d_lat') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$d_lng') ) + sin( radians('$d_lat') ) * sin( radians(latitude) ) ) ) as distance")
                            ->orderBy('distance')->first();
                        return $nearEnd ? $nearEnd->commune : null;
                    });
                }

                // 2. Bloquer si les communes de départ et d'arrivée sont différentes
                if ($start_commune && $end_commune && $start_commune !== $end_commune) {
                    return response()->json([
                        'error' => "Ce service est un service communal et est restreint à votre commune. Vous ne pouvez pas utiliser ce service pour sortir de votre commune.",
                        'start_commune' => $start_commune,
                        'end_commune' => $end_commune
                    ], 400);
                }

                // 3. Vérifier si le service appartient bien à cette commune (si spécifié)
                if ($serviceTypeModel->commune && $start_commune && strtolower($serviceTypeModel->commune) !== strtolower($start_commune)) {
                    return response()->json([
                        'error' => "Ce service est un service communal et est restreint à votre commune. Vous ne pouvez pas utiliser ce service pour sortir de votre commune.",
                        'start_commune' => $start_commune,
                        'service_commune' => $serviceTypeModel->commune
                    ], 400);
                }

                // 4. Sécurité supplémentaire de distance max
                $communalMaxDist = $serviceTypeModel->max_distance > 0 ? $serviceTypeModel->max_distance : 15.0;
                if ($UserRequest->distance > $communalMaxDist) {
                    return response()->json([
                        'error' => "Ce trajet ({$UserRequest->distance} km) dépasse le rayon d'action maximum autorisé pour ce service communal ({$communalMaxDist} km)."
                    ], 400);
                }
            }

            if (Auth::user()->wallet_balance > 0) {
                $UserRequest->use_wallet = $request->use_wallet ?: 0;
            }

            if ($request->use_karma == 1 && Auth::user()->social_points > 0) {
                $UserRequest->use_karma = 1;
                $UserRequest->karma_points_used = min((int)$request->karma_points, Auth::user()->social_points);
                
                // On déduit les points immédiatement pour éviter le double usage
                Auth::user()->decrement('social_points', $UserRequest->karma_points_used);
            }

            if (Setting::get('track_distance', 0) == 1) {
                $UserRequest->is_track = "YES";
            }
            $UserRequest->otp = $otp;
            $UserRequest->assigned_at = Carbon::now();
            $UserRequest->route_key = $route_key;
            $UserRequest->method = $request->input('method');
            $UserRequest->package_id = 0;
            if (!empty($request->package_id)) {
                $UserRequest->package_id = $request->package_id;
            } elseif (!empty($request->rental_package)) {
                $UserRequest->package_id = $request->rental_package;
            }

            if ($request->has('waypoints')) {
                $UserRequest->segments = json_decode($request->waypoints, true);
            }

            // --- GESTION DES DÉTAILS DE LIVRAISON (COLIS) ---
            if ($request->has('sender_name')) {
                $UserRequest->sender_name = $request->sender_name;
                $UserRequest->sender_phone = $request->sender_phone;
                $UserRequest->recipient_name = $request->receiver_name; // Mappe receiver -> recipient
                $UserRequest->recipient_phone = $request->receiver_phone;

                // On synthétise delivery_meta pour la compatibilité avec l'App Driver
                $deliveryMeta = [
                    'sender_name' => $request->sender_name,
                    'sender_phone' => $request->sender_phone,
                    'recipient_name' => $request->receiver_name, // Mappe receiver -> recipient
                    'recipient_mobile' => $request->receiver_phone, // Mappe receiver_phone -> recipient_mobile
                    'package_description' => $request->package_description ?? ''
                ];
                $UserRequest->delivery_meta = json_encode($deliveryMeta);
            }

            // [V2.3] Surge Engine - Vérification de la demande au moment de la création
            if ($UserRequest->s_geohash) {
                /** @var \App\Services\SurgeEngineService $surgeEngine */
                $surgeEngine = app(\App\Services\SurgeEngineService::class);
                $surgeFactor = $surgeEngine->getSurgeFactor($UserRequest->s_geohash);
                if ($surgeFactor > 1.05) {
                    $UserRequest->surge = 1;
                }
            } else {
                if ($Providers->count() <= Setting::get('surge_trigger') && $Providers->count() > 0) {
                    $UserRequest->surge = 1;
                }
            }

            if ($request->has('schedule_date') && $request->has('schedule_time')) {
                $UserRequest->schedule_at = date("Y-m-d H:i:s", strtotime("$request->schedule_date $request->schedule_time"));
            }
            if ($request->has('return_date') && $request->has('return_time')) {
                $UserRequest->return_date = $request->return_date;
                $UserRequest->return_time = $request->return_time;
            }

            if (isset($escrowFee) && $escrowFee > 0) {
                $UserRequest->escrow_fee = $escrowFee;
            }

            $UserRequest->save();

            // Dispatch job to compute polyline
            dispatch(new \App\Jobs\ComputeRoutePolylineJob(
                $UserRequest->id,
                $request->s_latitude, $request->s_longitude,
                $d_lat, $d_lng,
                $waypoints
            ));

            if ((Setting::get('manual_request', 0) == 0) && (Setting::get('broadcast_request', 0) == 0)) {
                Log::info('New Request id : ' . $UserRequest->id . ' Assigned to provider : ' . $UserRequest->current_provider_id);
                (new SendPushNotification)->IncomingRequest($Providers[0]->id, $UserRequest);
                
                // SMS fallback for offline drivers (sequential)
                try {
                    app(\App\Services\OfflineSmsDispatchService::class)->dispatchToOfflineProviders([$Providers[0]->id], $UserRequest);
                } catch (\Exception $e) {
                    Log::error("Error dispatching offline SMS in send_request (sequential): " . $e->getMessage());
                }
            }




            // update payment mode 

            User::where('id', Auth::user()->id)->update(['payment_mode' => $request->payment_mode]);

            if ($request->has('card_id')) {

                Card::where('user_id', Auth::user()->id)->update(['is_default' => 0]);
                Card::where('card_id', $request->card_id)->update(['is_default' => 1]);
            }

            if (Setting::get('manual_request', 0) == 0) {
                foreach ($Providers as $key => $Provider) {

                    if (Setting::get('broadcast_request', 0) == 1) {
                        (new SendPushNotification)->IncomingRequest($Provider->id, $UserRequest);
                    }

                    $Filter = new RequestFilter;
                    // Send push notifications to the first provider
                    // incoming request push to provider

                    $Filter->request_id = $UserRequest->id;
                    $Filter->provider_id = $Provider->id;
                    $Filter->save();
                }

                if (Setting::get('broadcast_request', 0) == 1) {
                    // SMS fallback for offline drivers (broadcast)
                    try {
                        app(\App\Services\OfflineSmsDispatchService::class)->dispatchToOfflineProviders(
                            $Providers->pluck('id')->toArray(),
                            $UserRequest
                        );
                    } catch (\Exception $e) {
                        Log::error("Error dispatching offline SMS in send_request (broadcast): " . $e->getMessage());
                    }
                }
            }

            if ($request->ajax()) {
                return response()->json([
                    'message' => 'New request Created!',
                    'request_id' => $UserRequest->id,
                    'current_provider' => $UserRequest->current_provider_id,
                ]);
            } else {
                return redirect('dashboard');
            }

        } catch (Exception $e) {
            \Log::error("Request Creation Error: " . $e->getMessage(), ['exception' => $e]);
            if ($request->ajax()) {
                return response()->json(['error' => trans('api.something_went_wrong'), 'message' => $e->getMessage()], 500);
            } else {
                return back()->with('flash_error', 'Something went wrong while sending request. Please try again.');
            }
        }
    }

    public function cancel_request(Request $request)
    {

        $this->validate($request, [
            'request_id' => 'required|numeric|exists:user_requests,id,user_id,' . Auth::user()->id,
            'cancel_reason' => 'nullable|max:255',
        ]);

        try {

            $UserRequest = UserRequests::findOrFail($request->request_id);

            if ($UserRequest->status == 'CANCELLED') {
                return response()->json(['error' => trans('api.ride.already_cancelled')], 400);
            }

            if (in_array($UserRequest->status, ['SEARCHING', 'ACCEPTED', 'STARTED', 'ARRIVED', 'SCHEDULED'])) {

                $oldStatus = $UserRequest->status;
                $assignedProviderId = (int) ($UserRequest->provider_id ?: $UserRequest->current_provider_id);

                $UserRequest->status = 'CANCELLED';
                $UserRequest->cancel_reason = $request->input('cancel_reason', '');
                $UserRequest->cancelled_by = 'USER';
                $UserRequest->save();

                // Remboursement karma si déduit à la création
                if ($UserRequest->use_karma && $UserRequest->karma_points_used > 0) {
                    Auth::user()->increment('social_points', $UserRequest->karma_points_used);
                }

                // GESTION DU SÉQUESTRE LOGISTIQUE (LOCATION)
                if ($UserRequest->escrow_fee > 0) {
                    if ($oldStatus != 'SEARCHING') {
                        $driverShareCfa = $UserRequest->escrow_fee * 0.60;
                        $driverShareEco = $driverShareCfa / 1000.0;

                        $provider = \App\Provider::find($assignedProviderId);
                        if ($provider) {
                            $provider->increment('eco_wallet_balance', $driverShareEco);

                            \App\ProviderWallet::create([
                                'provider_id' => $provider->id,
                                'amount' => $driverShareCfa,
                                'transaction_id' => 'ESCROW_PENALTY_' . $UserRequest->id,
                                'transaction_desc' => 'Dédommagement annulation client (Séquestre)',
                                'type' => 'CREDIT',
                                'balance' => $provider->eco_wallet_balance,
                            ]);
                        }
                    }
                }

                RequestFilter::where('request_id', $UserRequest->id)->delete();

                if ($oldStatus != 'SCHEDULED' && $assignedProviderId > 0) {
                    $activeTripsCount = UserRequests::where('provider_id', $assignedProviderId)
                        ->whereIn('status', ['SEARCHING', 'ACCEPTED', 'STARTED', 'ARRIVED', 'PICKEDUP', 'DROPPED'])
                        ->count();
                    if ($activeTripsCount == 0) {
                        ProviderService::where('provider_id', $assignedProviderId)->update(['status' => 'active']);
                    }
                }

                try {
                    (new SendPushNotification)->UserCancellRide($UserRequest);
                } catch (\Exception $e) {
                    Log::warning('Push cancel failed for request ' . $UserRequest->id . ': ' . $e->getMessage());
                }

                return response()->json(['message' => trans('api.ride.ride_cancelled')]);

            } else {
                return response()->json(['error' => trans('api.ride.already_onride')], 400);
            }
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => trans('api.something_went_wrong')], 404);
        } catch (\Exception $e) {
            Log::error('Cancel request error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => trans('api.something_went_wrong'), 'message' => $e->getMessage()], 500);
        }

    }

    public function request_status_check()
    {

        try {
            $check_status = ['CANCELLED', 'SCHEDULED'];

            $UserRequests = UserRequests::UserRequestStatusCheck(Auth::user()->id, $check_status)
                ->get()
                ->toArray();

            $search_status = ['SEARCHING', 'SCHEDULED'];
            $UserRequestsFilter = UserRequests::UserRequestAssignProvider(Auth::user()->id, $search_status)->get();

            // Ajout des infos de "Dispatch Enchaîné" pour rassurer l'utilisateur
            foreach ($UserRequests as &$request) {
                // Check if there is an active offline SMS booking pending for this request
                $request['sms_pending'] = \App\OfflineBookingSms::where('request_id', $request['id'])
                    ->where('status', 'PENDING')
                    ->exists();

                // Guard: Le Dispatch Enchaîné ne concerne QUE les courses privées.
                // Pour dynamique/arret, le chauffeur peut avoir des passagers et c'est normal.
                $requestVariant = strtolower($request['ride_variant'] ?? 'prive');
                $isPrivate = ($requestVariant === 'prive');

                if ($isPrivate && isset($request['provider_id']) && $request['provider_id'] > 0 && in_array($request['status'], ['ACCEPTED', 'STARTED', 'ARRIVED'])) {
                    
                    // Vérifier si ce chauffeur a une course en cours (PICKEDUP) qui n'est pas celle-ci
                    $activeTrip = \App\UserRequests::where('provider_id', $request['provider_id'])
                        ->where('status', 'PICKEDUP')
                        ->where('id', '!=', $request['id'])
                        ->first();

                    if ($activeTrip) {
                        $request['chained_info'] = [
                            'is_chained'      => true,
                            'message'         => "Votre chauffeur termine une course à proximité.",
                            'current_trip_id' => $activeTrip->id
                        ];

                        // Calculer l'ETA total avec getRouteEstimate() (méthode correcte)
                        try {
                            $routing = new \App\Services\DispatchEngine\RoutingService();
                            
                            // 1. Temps restant pour finir la course actuelle (position du chauffeur → dépose A)
                            $driverLat = $activeTrip->track_latitude ?: $activeTrip->s_latitude;
                            $driverLng = $activeTrip->track_longitude ?: $activeTrip->s_longitude;
                            $cacheKeyCurrent = 'osrm_route:' . md5("{$driverLat},{$driverLng},{$activeTrip->d_latitude},{$activeTrip->d_longitude}");
                            $cacheKeyNext = 'osrm_route:' . md5("{$activeTrip->d_latitude},{$activeTrip->d_longitude},{$request['s_latitude']},{$request['s_longitude']}");
                            
                            $routeCurrent = \Cache::get($cacheKeyCurrent);
                            $routeNext = \Cache::get($cacheKeyNext);

                            if (!$routeCurrent || !$routeNext) {
                                // Fallback géométrique immédiat pour ne pas bloquer l'API
                                $distCurrent = 6371 * acos(cos(deg2rad($driverLat)) * cos(deg2rad($activeTrip->d_latitude)) * cos(deg2rad($activeTrip->d_longitude) - deg2rad($driverLng)) + sin(deg2rad($driverLat)) * sin(deg2rad($activeTrip->d_latitude)));
                                $distNext = 6371 * acos(cos(deg2rad($activeTrip->d_latitude)) * cos(deg2rad($request['s_latitude'])) * cos(deg2rad($request['s_longitude']) - deg2rad($activeTrip->d_longitude)) + sin(deg2rad($activeTrip->d_latitude)) * sin(deg2rad($request['s_latitude'])));
                                
                                $routeCurrent = $routeCurrent ?: ['duration_min' => ($distCurrent / 30) * 60];
                                $routeNext = $routeNext ?: ['duration_min' => ($distNext / 30) * 60];
                                
                                // Lancer le job OSRM en arrière-plan
                                dispatch(new \App\Jobs\ComputeChainedETAJob(
                                    $driverLat, $driverLng,
                                    $activeTrip->d_latitude, $activeTrip->d_longitude,
                                    $request['s_latitude'], $request['s_longitude'],
                                    $cacheKeyCurrent, $cacheKeyNext
                                ));
                            }

                            $secsCurrent = $routeCurrent ? ($routeCurrent['duration_min'] * 60) : 600;
                            $secsNext    = $routeNext    ? ($routeNext['duration_min']    * 60) : 600;
                            $totalMinutes = (int) ceil(($secsCurrent + $secsNext) / 60);

                            $request['chained_info']['wait_time']     = $totalMinutes;
                            $request['chained_info']['total_seconds'] = (int) ($secsCurrent + $secsNext);
                        } catch (\Exception $e) {
                            \Log::error("Chained ETA Error: " . $e->getMessage());
                            $request['chained_info']['wait_time']     = 10; // Fallback
                            $request['chained_info']['total_seconds'] = 600;
                        }
                    } else {
                        $request['chained_info'] = ['is_chained' => false];
                    }
                } else {
                    // Variante partagée ou arrêt → pas de chained_info (comportement normal)
                    $request['chained_info'] = ['is_chained' => false];
                }
            }

            $Timeout = \Setting::get('provider_select_timeout', 180);

            if (!empty($UserRequestsFilter)) {
                for ($i = 0; $i < sizeof($UserRequestsFilter); $i++) {
                    $ExpiredTime = $Timeout - (time() - strtotime($UserRequestsFilter[$i]->assigned_at));
                    if ($UserRequestsFilter[$i]->status == 'SEARCHING' && $ExpiredTime < 0) {
                        $Providertrip = new \App\Http\Controllers\ProviderResources\TripController();
                        $Providertrip->assign_next_provider($UserRequestsFilter[$i]->id);
                    } else if ($UserRequestsFilter[$i]->status == 'SEARCHING' && $ExpiredTime > 0) {
                        break;
                    }
                }
            }

            return response()->json(['data' => $UserRequests]);

        } catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')], 500);
        }
    }

    public function rate_provider(Request $request)
    {

        $this->validate($request, [
            'request_id' => 'required|integer|exists:user_requests,id,user_id,' . Auth::user()->id,
            'rating' => 'required|integer|in:1,2,3,4,5',
            'comment' => 'max:255',
        ]);

        $UserRequests = UserRequests::where('id', $request->request_id)
            ->where('status', 'COMPLETED')
            ->where('paid', 0)
            ->first();

        if ($UserRequests) {
            return response()->json(['error' => trans('api.user.not_paid')], 500);
        }

        try {

            $UserRequest = UserRequests::findOrFail($request->request_id);

            if ($UserRequest->rating == null) {
                UserRequestRating::create([
                    'provider_id' => $UserRequest->provider_id,
                    'user_id' => $UserRequest->user_id,
                    'request_id' => $UserRequest->id,
                    'user_rating' => $request->rating,
                    'user_comment' => $request->comment,
                ]);
            } else {
                $UserRequest->rating->update([
                    'user_rating' => $request->rating,
                    'user_comment' => $request->comment,
                ]);
            }

            $UserRequest->user_rated = 1;
            $UserRequest->save();

            $average = UserRequestRating::where('provider_id', $UserRequest->provider_id)->avg('user_rating');

            Provider::where('id', $UserRequest->provider_id)->update(['rating' => $average]);

            // Send Push Notification to Provider 
            return response()->json(['message' => trans('api.ride.request_rated')]);
        } catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')], 500);

        }

    }

    public function modifiy_request(Request $request)
    {

        $this->validate($request, [
            'request_id' => 'required|integer|exists:user_requests,id,user_id,' . Auth::user()->id,
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'address' => 'required'
        ]);

        try {

            $UserRequest = UserRequests::findOrFail($request->request_id);
            $UserRequest->d_latitude = $request->latitude ?: $UserRequest->d_latitude;
            $UserRequest->d_longitude = $request->longitude ?: $UserRequest->d_longitude;
            $UserRequest->d_address = $request->address ?: $UserRequest->d_address;
            $UserRequest->save();

            // Send Push Notification to Provider 
            return response()->json(['message' => trans('api.ride.request_modify_location')]);
        } catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')], 500);
        }

    }

    public function show_providers(Request $request)
    {

        $this->validate($request, [
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'service' => 'sometimes|numeric',
        ]);

        try {

            $distance = Setting::get('provider_search_radius', '10');
            $latitude = $request->latitude;
            $longitude = $request->longitude;

            if ($request->has('service') && $request->service != -1) {
                $serviceType = ServiceType::find($request->service);
                $ActiveProviders = ProviderService::AvailableServiceProvider($request->service)
                    ->get()->pluck('provider_id');

                $lat_deg = $distance / 111.0;
                $lng_deg = $distance / (111.0 * cos(deg2rad($latitude)));
                $min_lat = $latitude - $lat_deg;
                $max_lat = $latitude + $lat_deg;
                $min_lng = $longitude - $lng_deg;
                $max_lng = $longitude + $lng_deg;

                $query = Provider::with('service')->whereIn('id', $ActiveProviders)
                    ->where('status', 'approved')
                    ->whereBetween('latitude', [$min_lat, $max_lat])
                    ->whereBetween('longitude', [$min_lng, $max_lng])
                    ->whereRaw("(1.609344 * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance");

                // Antigravity: Filtrage Communal pour la carte (Nearby Providers)
                if ($serviceType && $serviceType->is_communal) {
                    $nearStart = \App\PdpStop::join('communes', 'pdp_stops.commune_id', '=', 'communes.id')
                        ->whereRaw("(1.609344 * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= 5.0")
                        ->selectRaw("communes.commune as commune, (1.609344 * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) as distance")
                        ->orderBy('distance')->first();

                    if ($nearStart && $nearStart->commune) {
                        $startCommune = $nearStart->commune;
                        $query->where(function ($q) use ($startCommune) {
                            $q->where('commune', $startCommune)
                                ->orWhereNull('commune');
                        });
                        Log::info("Communal map filter (Smart) applied for " . $startCommune);
                    }
                }

                $Providers = $query->get();

            } else {

                $ActiveProviders = ProviderService::where('status', 'active')
                    ->get()->pluck('provider_id');

                $lat_deg = $distance / 111.0;
                $lng_deg = $distance / (111.0 * cos(deg2rad($latitude)));
                $min_lat = $latitude - $lat_deg;
                $max_lat = $latitude + $lat_deg;
                $min_lng = $longitude - $lng_deg;
                $max_lng = $longitude + $lng_deg;

                $Providers = Provider::with('service')->whereIn('id', $ActiveProviders)
                    ->where('status', 'approved')
                    ->whereBetween('latitude', [$min_lat, $max_lat])
                    ->whereBetween('longitude', [$min_lng, $max_lng])
                    ->whereRaw("(1.609344 * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance")
                    ->get();
            }


            return response()->json($Providers);

        } catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')], 500);
        }
    }

    public function confirmCashPayment(Request $request, $id)
    {
        try {
            $UserRequest = UserRequests::where('id', $id)
                ->where('user_id', Auth::user()->id)
                ->whereIn('status', ['DROPPED', 'PAYMENT'])
                ->where('payment_mode', 'CASH')
                ->firstOrFail();

            // Marquer comme complété du côté passager
            $UserRequest->status   = 'COMPLETED';
            $UserRequest->paid     = 1;
            $UserRequest->save();

            // Générer la facture si elle n'existe pas encore
            if (!$UserRequest->payment()->exists()) {
                $tripController = new \App\Http\Controllers\ProviderResources\TripController();
                $tripController->invoice($id);
            }

            return response()->json([
                'message' => 'Paiement confirmé avec succès.',
                'status'  => 'COMPLETED'
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur confirmCashPayment: ' . $e->getMessage());
            return response()->json(['error' => 'Impossible de confirmer le paiement ou la course n\'est pas éligible.'], 500);
        }
    }

}