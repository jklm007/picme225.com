<?php

namespace App\Http\Controllers\ProviderResources;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use App\Http\Controllers\Controller;

use Auth;
use Setting;
use Storage;
use Exception;
use Carbon\Carbon;

use App\Models\ProviderProfile;
use App\Models\UserRequests;
use App\Models\ProviderService;
use App\Models\Fleet;
use App\Models\RequestFilter;

class ProfileController extends Controller
{
    /**
     * Create a new user instance.
     *
     * @return void
     */

    // public function __construct()
    // {
    //       $this->middleware('provider.api', ['except' => ['show', 'store', 'available', 'location_edit', 'location_update']]);
    //  }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        \Log::info("ProfileController@index: Request received. Guard user: " . (Auth::guard('providerapi')->check() ? 'authenticated' : 'NOT authenticated'));
        try {
            $user = Auth::guard('providerapi')->user();
            if (!$user) {
                \Log::error("ProfileController@index: Auth::guard('providerapi')->user() returned null!");
                return response()->json(['error' => 'Not authenticated'], 401);
            }
            \Log::info("ProfileController@index: Loading relations for provider ID " . $user->id);
            $user->load(['service.service_type.services', 'subscriptionPlan', 'profile', 'selectedServices']);
            \Log::info("ProfileController@index: Relations loaded successfully.");

            // wallet_balance = solde CFA réel (retirable)
            // eco_wallet_balance = solde ECO (commissions, non retirable)
            // NE PAS écraser wallet_balance — il doit refléter le vrai CFA

            $user->fleet_details = Fleet::find($user->fleet);
            $user->currency = Setting::get('currency', 'CFA');
            $user->sos = Setting::get('sos_number', '111');
            $user->eco_currency_name = Setting::get('eco_currency_name', 'ECO');

            // Logic pour les badges de niveau (DAO)
            $level = $user->subscriptionPlan ? $user->subscriptionPlan->name : 'STANDARD';
            $user->level = strtoupper($level);
            $user->max_categories = $user->subscriptionPlan ? $user->subscriptionPlan->max_categories : 1;
            // eco_token_balance est un alias de eco_wallet_balance pour compatibilité
            $user->eco_token_balance = $user->eco_wallet_balance;

            // Système de réputation et badges pour le Chauffeur
            $socialPoints = $user->social_points ?? 0;
            $rating = $user->rating ?? 5.0;
            $reputationScore = ($socialPoints * 10) + ($rating * 100);
            
            $badge = 'Conducteur Novice 🚗';
            if ($reputationScore >= 1000 && strtoupper($level) === 'GOLD') {
                $badge = '👑 Légende de la Route';
            } elseif ($reputationScore >= 700 && (strtoupper($level) === 'PRO' || strtoupper($level) === 'GOLD') && $rating >= 4.7) {
                $badge = '💎 Ambassadeur Elite';
            } elseif ($reputationScore >= 400 && $rating >= 4.5) {
                $badge = '⭐ Chauffeur Influent';
            } elseif ($reputationScore >= 150 || $rating >= 4.0) {
                $badge = '✅ Chauffeur Recommandé';
            }
            
            // Direct database update to prevent Eloquent from saving temporary non-DB attributes
            \DB::table('providers')->where('id', $user->id)->update([
                'user_badge' => $badge,
                'social_points' => $socialPoints,
            ]);

            $user->user_badge = $badge;
            $user->social_points = $socialPoints;

            // On définit dynamiquement l'URL du badge
            $user->level_badge = asset('asset/img/badges/' . strtolower($level) . '.png');
            $user->reviews = \App\Models\UserRequestRating::where('provider_id', $user->id)
                ->whereNotNull('provider_comment')
                ->latest()
                ->take(10)
                ->get();

            // Formater les services pour le driver
            if ($user->service && $user->service->service_type) {
                // ... (reste du code identique pour la gestion des services)
                $allowedServices = $user->service->service_type->services;
                $selectedIds = $user->selectedServices->where('is_active', true)->pluck('service_id')->toArray();

                if ($user->selectedServices->count() == 0) {
                    foreach ($allowedServices as $s) {
                        $selectedIds[] = $s->id;
                    }
                }

                // Un chauffeur est considéré comme ayant un abonnement actif s'il a une date d'expiration valide dans le futur (gère aussi l'essai gratuit de 3 mois)
                $hasAnySubscription = ($user->subscription_expires_at && Carbon::now()->lt($user->subscription_expires_at));

                $user->available_categories = $allowedServices->map(function ($service) use ($selectedIds, $hasAnySubscription, $user) {
                    return [
                        'id' => $service->id,
                        'name' => $service->name,
                        'image' => $service->image,
                        'is_selected' => in_array($service->id, $selectedIds),
                        'has_subscription' => $hasAnySubscription,
                        'optPrivateRide' => (bool)$user->opt_private_ride,
                        'optShareRide' => (bool)$user->opt_share_ride,
                        'optMultiStop' => (bool)$user->opt_multi_stop,
                        'optArretRide' => (bool)$user->opt_arret_ride,
                        'rental_driver_preference' => $user->service ? $user->service->rental_driver_preference : 'WITH_DRIVER'
                    ];
                });
            }

            return response()->json($user);

        } catch (Exception $e) {
            \Log::error("ProfileController@index exception: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update the driver's selected service categories.
     */
    public function update_service_selection(Request $request)
    {
        $this->validate($request, [
            'services' => 'required|array',
            'services.*' => 'exists:services,id',
            'rental_driver_preference' => 'nullable|string|in:WITH_DRIVER,WITHOUT_DRIVER,BOTH'
        ]);

        $provider = Auth::guard('providerapi')->user();
        $provider->load(['service']);

        if (!$provider->service || !$provider->service->service_type) {
            return response()->json(['error' => 'Vehicle not configured'], 400);
        }

        // Get allowed service IDs for this vehicle
        $allowedIds = $provider->service->service_type->services->pluck('id')->toArray();

        // Filter input to only allowed services
        $selectedIds = array_intersect($request->services, $allowedIds);

        if (empty($selectedIds)) {
            return response()->json(['error' => 'You must select at least one valid service'], 400);
        }

        // --- ENFORCEMENT DES LIMITES D'ABONNEMENT ---
        // Le nombre total de catégories sélectionnées ne doit pas dépasser le quota max_categories de la formule
        $plan = $provider->subscriptionPlan;
        $maxCategories = $plan ? $plan->max_categories : 1; // 1 par défaut pour FREE

        if (count($selectedIds) > $maxCategories) {
            $planName = $plan ? $plan->name : 'GRATUIT';
            return response()->json([
                'error' => "Votre abonnement actuel ({$planName}) vous permet d'activer au maximum {$maxCategories} catégorie(s). Vous essayez d'activer " . count($selectedIds) . " catégories. Veuillez changer d'abonnement pour en activer plus.",
                'limit_reached' => true,
                'max_allowed' => $maxCategories,
                'current_selection' => count($selectedIds)
            ], 422);
        }

        // Sauvegarde de la préférence de location si la catégorie Location est sélectionnée
        $locationService = \App\Models\Service::where('name', 'Location')->first();
        if ($locationService && in_array($locationService->id, $selectedIds)) {
            $pref = $request->input('rental_driver_preference', 'WITH_DRIVER');
            $provider->service->rental_driver_preference = $pref;
            $provider->service->save();
        }

        // Sauvegarde des options du chauffeur
        if ($request->has('options')) {
            $options = $request->input('options');
            $provider->opt_private_ride = filter_var($options['private_ride'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $provider->opt_share_ride = filter_var($options['share_ride'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $provider->opt_multi_stop = filter_var($options['multi_stop'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $provider->opt_arret_ride = filter_var($options['arret_ride'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $provider->save();
        }

        // Update selected services
        \App\Models\ProviderSelectedService::where('provider_id', $provider->id)->delete();

        foreach ($selectedIds as $id) {
            \App\Models\ProviderSelectedService::create([
                'provider_id' => $provider->id,
                'service_id' => $id,
                'is_active' => true
            ]);
        }

        return response()->json([
            'message' => 'Service selection updated successfully',
            'selected_ids' => $selectedIds,
            'rental_driver_preference' => $provider->service->rental_driver_preference ?? 'WITH_DRIVER'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'mobile' => 'required',
            'driver_license_no' => 'sometimes|nullable|string|max:255',
            'avatar' => 'mimes:jpeg,bmp,png',
            'language' => 'max:255',
            'address' => 'max:255',
            'address_secondary' => 'max:255',
            'city' => 'max:255',
            'country' => 'max:255',
            'postal_code' => 'max:255',
        ]);

        try {

            $Provider = Auth::user();

            if ($request->has('first_name'))
                $Provider->first_name = $request->first_name;

            if ($request->has('last_name'))
                $Provider->last_name = $request->last_name;

            if ($request->has('display_name'))
                $Provider->display_name = $request->display_name;

            if ($request->has('mobile'))
                $Provider->mobile = $request->mobile;

            if ($request->has('driver_license_no'))
                $Provider->driver_license_no = $request->driver_license_no;

            if ($request->has('language'))
                $Provider->language = $request->language;

            if ($request->hasFile('avatar')) {
                if (!empty($Provider->avatar)) {
                    Storage::delete($Provider->avatar);
                }
                $Provider->avatar = $request->avatar->store('provider/profile');
            }

            if ($request->has('service_type')) {
                if ($Provider->service) {
                    if ($Provider->service->service_type_id != $request->service_type) {
                        $Provider->status = 'banned';
                    }
                    //$ProviderService = ProviderService::where('provider_id',Auth::user()->id);
                    $Provider->service->service_type_id = $request->service_type;
                    $Provider->service->service_number = $request->service_number;
                    $Provider->service->service_model = $request->service_model;
                    if ($request->has('hospital_id') && $request->hospital_id != '') {
                        $Provider->service->hospital_id = $request->hospital_id;
                    } else {
                        $Provider->service->hospital_id = 0;
                    }
                    if (isset($request->document_url)) {
                        $Provider->service->document_url = $request->document_url->store('provider/hospitaldocuments');

                    } else {
                        $Provider->service->document_url = 'NULL';
                    }
                    $Provider->service->save();

                } else {
                    if ($request->has('document_url')) {
                        $document = $request->document_url->store('provider/hospitaldocuments');
                    } else {
                        $document = 'NULL';
                    }
                    ProviderService::create([
                        'provider_id' => $Provider->id,
                        'service_type_id' => $request->service_type,
                        'service_number' => $request->service_number,
                        'service_model' => $request->service_model,
                        'hospital_id' => $request->hospital_id ?: '0',
                        'document_url' => $document,
                    ]);
                    $Provider->status = 'banned';
                }
            }

            if ($Provider->profile) {
                $Provider->profile->update([
                    'language' => $request->language ?: $Provider->profile->language,
                    'address' => $request->address ?: $Provider->profile->address,
                    'address_secondary' => $request->address_secondary ?: $Provider->profile->address_secondary,
                    'city' => $request->city ?: $Provider->profile->city,
                    'country' => $request->country ?: $Provider->profile->country,
                    'postal_code' => $request->postal_code ?: $Provider->profile->postal_code,
                ]);
            } else {
                ProviderProfile::create([
                    'provider_id' => $Provider->id,
                    'language' => $request->language,
                    'address' => $request->address,
                    'address_secondary' => $request->address_secondary,
                    'city' => $request->city,
                    'country' => $request->country,
                    'postal_code' => $request->postal_code,
                ]);
            }


            $Provider->save();

            return redirect(route('provider.profile.index'));
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Provider Not Found!'], 404);
        }
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        return view('provider.profile.index');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $this->validate($request, [
            'first_name' => 'sometimes|required|max:255',
            'last_name' => 'sometimes|required|max:255',
            'mobile' => 'sometimes|required',
            'driver_license_no' => 'sometimes|nullable|string|max:255',
            'avatar' => 'sometimes|mimes:jpeg,bmp,png',
            'language' => 'sometimes|max:255',
            'address' => 'sometimes|max:255',
            'address_secondary' => 'sometimes|max:255',
            'city' => 'sometimes|max:255',
            'country' => 'sometimes|max:255',
            'postal_code' => 'sometimes|max:255',
            'service_type' => 'nullable|exists:service_types,id',
            'service_number' => 'required_with:service_type',
            'service_model' => 'required_with:service_type',
            'vehicle_picture' => 'nullable|mimes:jpeg,bmp,png',
        ]);

        try {

            $Provider = Auth::user();

            if ($request->has('first_name'))
                $Provider->first_name = $request->first_name;

            if ($request->has('last_name'))
                $Provider->last_name = $request->last_name;

            if ($request->has('display_name'))
                $Provider->display_name = $request->display_name;

            if ($request->has('wallet_address'))
                $Provider->wallet_address = $request->wallet_address;

            if ($request->has('mobile'))
                $Provider->mobile = $request->mobile;

            if ($request->has('driver_license_no'))
                $Provider->driver_license_no = $request->driver_license_no;

            if ($request->hasFile('avatar')) {
                if (!empty($Provider->avatar)) {
                    Storage::delete($Provider->avatar);
                }
                $Provider->avatar = $request->avatar->store('provider/profile');
            }

            // Gestion du changement de véhicule
            if ($request->has('service_type')) {
                $statusChange = false;

                if (!$Provider->service) {
                    $Provider->service = new ProviderService();
                    $Provider->service->provider_id = $Provider->id;
                    $statusChange = true;
                } elseif (
                    $Provider->service->service_type_id != $request->service_type ||
                    $Provider->service->service_number != $request->service_number
                ) {
                    $statusChange = true;
                }

                $Provider->service->service_type_id = $request->service_type;
                $Provider->service->service_number = $request->service_number;
                $Provider->service->service_model = $request->service_model;

                if ($request->hasFile('vehicle_picture')) {
                    if ($Provider->service && !empty($Provider->service->document_url) && $Provider->service->document_url !== 'NULL') {
                        Storage::delete($Provider->service->document_url);
                    }
                    $Provider->service->document_url = $request->vehicle_picture->store('provider/vehicles');
                    $statusChange = true;
                }

                $Provider->service->save();

                if ($statusChange) {
                    $Provider->status = 'onboarding'; // Attente de validation
                }
            }

            if ($Provider->profile) {
                // ... (reste de la mise à jour du profil)
                $Provider->profile->update([
                    'language' => $request->language ?: $Provider->profile->language,
                    'address' => $request->address ?: $Provider->profile->address,
                    'address_secondary' => $request->address_secondary ?: $Provider->profile->address_secondary,
                    'city' => $request->city ?: $Provider->profile->city,
                    'country' => $request->country ?: $Provider->profile->country,
                    'postal_code' => $request->postal_code ?: $Provider->profile->postal_code,
                ]);
            } else {
                ProviderProfile::create([
                    'provider_id' => $Provider->id,
                    'language' => $request->language,
                    'address' => $request->address,
                    'address_secondary' => $request->address_secondary,
                    'city' => $request->city,
                    'country' => $request->country,
                    'postal_code' => $request->postal_code,
                ]);
            }

            // Système de réputation et badges pour le Chauffeur
            $level = $Provider->subscriptionPlan ? $Provider->subscriptionPlan->name : 'STANDARD';
            $socialPoints = $Provider->social_points ?? 0;
            $rating = $Provider->rating ?? 5.0;
            $reputationScore = ($socialPoints * 10) + ($rating * 100);
            
            $badge = 'Conducteur Novice 🚗';
            if ($reputationScore >= 1000 && strtoupper($level) === 'GOLD') {
                $badge = '👑 Légende de la Route';
            } elseif ($reputationScore >= 700 && (strtoupper($level) === 'PRO' || strtoupper($level) === 'GOLD') && $rating >= 4.7) {
                $badge = '💎 Ambassadeur Elite';
            } elseif ($reputationScore >= 400 && $rating >= 4.5) {
                $badge = '⭐ Chauffeur Influent';
            } elseif ($reputationScore >= 150 || $rating >= 4.0) {
                $badge = '✅ Chauffeur Recommandé';
            }
            
            // Save real columns to database
            $Provider->user_badge = $badge;
            $Provider->social_points = $socialPoints;
            $Provider->save();

            // Now safely load relations and assign dynamic attributes for response
            $Provider = $Provider->load(['service', 'profile']);
            $Provider->level = strtoupper($level);
            // eco_token_balance = alias de eco_wallet_balance pour compatibilité API
            // wallet_balance reste intact (solde CFA réel, retirable)
            $Provider->eco_token_balance = $Provider->eco_wallet_balance;
            return $Provider;
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Provider Not Found!'], 404);
        }
    }

    /**
     * Update latitude and longitude of the user.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function location(Request $request)
    {
        $this->validate($request, [
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        if ($Provider = Auth::guard('providerapi')->user()) {
            $cacheKey = "provider_location_db_write:{$Provider->id}";
            $Provider->latitude = $request->latitude;
            $Provider->longitude = $request->longitude;
            if (!\Cache::has($cacheKey)) {
                $Provider->save();
                \Cache::put($cacheKey, true, now()->addMinutes(2)); // Write at most every 2 min
            }

            return response()->json(['message' => 'Location Updated successfully!']);

        } else {
            return response()->json(['error' => 'Provider Not Found!']);
        }
    }

    /**
     * Toggle service availability of the provider.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function available(Request $request)
    {
        $this->validate($request, [
            'service_status' => 'required|in:active,offline',
        ]);

        $Provider = Auth::guard('providerapi')->user() ?: Auth::guard('provider')->user();

        if ($Provider && $Provider->service) {

            $provider = $Provider->id;
            $OfflineOpenRequest = RequestFilter::with(['request.provider', 'request'])
                ->where('provider_id', $provider)
                ->whereHas('request', function ($query) use ($provider) {
                    $query->where('status', 'SEARCHING');
                    $query->where('current_provider_id', '<>', $provider);
                    $query->orWhereNull('current_provider_id');
                })->pluck('id');

            if (count($OfflineOpenRequest) > 0) {
                RequestFilter::whereIn('id', $OfflineOpenRequest)->delete();
            }

            $Provider->service->update(['status' => $request->service_status]);
        } else {
            return response()->json(['error' => 'You account has not been approved for driving']);
        }

        return $Provider->load('service');
    }

    /**
     * Update password of the provider.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function password(Request $request)
    {
        $this->validate($request, [
            'password' => 'required|confirmed',
            'password_old' => 'required',
        ]);

        $Provider = Auth::guard('providerapi')->user();

        if (password_verify($request->password_old, $Provider->password)) {
            $Provider->password = bcrypt($request->password);
            $Provider->save();

            return response()->json(['message' => 'Password changed successfully!']);
        } else {
            return response()->json(['error' => 'Required is new password should not be same as old password'], 422);
        }
    }

    /**
     * Show providers daily target.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function target(Request $request)
    {
        try {

            $Rides = UserRequests::where('provider_id', Auth::guard('providerapi')->user()->id)
                ->whereIn('status', ['COMPLETED', 'CANCELLED'])
                ->whereDate('created_at', '>=', Carbon::today())
                ->with('payment', 'service_type')
                ->get();

            $total_distance = $Rides->sum('distance');
            $total_duration = $Rides->sum('travel_time');

            return response()->json([
                'rides' => $Rides,
                'rides_count' => $Rides->count(),
                'total_distance' => $total_distance,
                'total_duration' => $total_duration,
                'target' => Setting::get('daily_target', '0')
            ]);

        } catch (Exception $e) {
            return response()->json(['error' => "Something Went Wrong"]);
        }
    }

    /**
     * Update the driver's Smart Mode settings.
     */
    public function update_smart_mode(Request $request)
    {
        // Fix for Android Retrofit sending "true"/"false" as strings
        if ($request->has('is_smart_mode')) {
            $request->merge([
                'is_smart_mode' => filter_var($request->is_smart_mode, FILTER_VALIDATE_BOOLEAN)
            ]);
        }

        $this->validate($request, [
            'is_smart_mode' => 'required|boolean',
            'smart_mode_type' => 'required_if:is_smart_mode,true|in:HOME,ZONE,COMMUNE,STATION,WORO_FREE,WORO_FIXED',
            'smart_dest_lat' => 'nullable|numeric',
            'smart_dest_lng' => 'nullable|numeric',
            'smart_dest_address' => 'nullable|string',
            'smart_zone_radius' => 'nullable|numeric|min:1|max:50',
            'smart_communes' => 'nullable'
        ]);

        $provider = Auth::guard('providerapi')->user();

        // Check quota if enabling
        $is_woro = in_array($request->smart_mode_type, ['WORO_FREE', 'WORO_FIXED']);
        $max_quota = ($provider->rating >= 4.5) ? 5 : 3;

        if ($request->is_smart_mode && !$provider->is_smart_mode && !$is_woro) {
            $today = Carbon::today();
            if ($provider->smart_last_used_at && !Carbon::parse($provider->smart_last_used_at)->isSameDay($today)) {
                $provider->smart_quota_count = 0;
            }

            if ($provider->smart_quota_count >= $max_quota) {
                // If limit reached, try to charge 100 CFA from eco_wallet_balance
                if ($provider->eco_wallet_balance >= 100) {
                    $provider->eco_wallet_balance -= 100;
                    try {
                        \App\Models\ProviderWallet::create([
                            'provider_id' => $provider->id,
                            'transaction_id' => 'SMART_' . $provider->id . '_' . time(),
                            'transaction_desc' => 'Frais activation extra Smart Mode',
                            'type' => 'DEBIT',
                            'amount' => -100,
                            'balance' => $provider->eco_wallet_balance
                        ]);
                    } catch(\Exception $e) {
                        \Log::error("Smart Mode Wallet Log Error: " . $e->getMessage());
                    }
                } else {
                    return response()->json([
                        'error' => "Limite quotidienne atteinte ($max_quota). Solde insuffisant (100 CFA requis) pour une activation supplémentaire."
                    ], 403);
                }
            }

            $provider->smart_quota_count += 1;
            $provider->smart_last_used_at = Carbon::now();
        }

        $provider->is_smart_mode = $request->is_smart_mode;

        if ($request->is_smart_mode) {
            $provider->smart_mode_type = $request->smart_mode_type;
            if ($request->has('smart_dest_lat'))
                $provider->smart_dest_lat = $request->smart_dest_lat;
            if ($request->has('smart_dest_lng'))
                $provider->smart_dest_lng = $request->smart_dest_lng;
            if ($request->has('smart_dest_address'))
                $provider->smart_dest_address = $request->smart_dest_address;
            if ($request->has('smart_zone_radius'))
                $provider->smart_zone_radius = $request->smart_zone_radius;
            if ($request->has('smart_communes')) {
                $provider->smart_communes = is_array($request->smart_communes)
                    ? json_encode($request->smart_communes)
                    : $request->smart_communes;
            }
        }

        $provider->save();

        return response()->json([
            'message' => 'Smart Mode updated successfully',
            'provider' => $provider,
            'smart_max_quota' => $max_quota,
            'smart_is_woro' => $is_woro
        ]);
    }

    // =========================================================================
    //  [V2.3] GPS PING — Endpoint Anti-Fraude
    //  POST /api/provider/gps-ping
    //  L'app Android envoie un batch de pings GPS enrichis toutes les 30s.
    //  Le FraudDetectionService calcule et cache le MovementIntegrityScore.
    // =========================================================================

    /**
     * Reçoit un batch de pings GPS du chauffeur et calcule son MIS.
     *
     * Body JSON attendu :
     * {
     *   "pings": [
     *     {
     *       "lat": 5.36, "lng": -4.008,
     *       "speed_kmh": 42.5, "accuracy_meters": 8,
     *       "is_mock_location": false, "sensor_timestamp": 1716000000,
     *       "device_fingerprint_hash": "a3f9e8c2...", "network_type": "4G"
     *     }, ...
     *   ]
     * }
     */
    public function gps_ping(Request $request)
    {
        $provider = Auth::guard('providerapi')->user();

        $pings = $request->input('pings', []);

        if (empty($pings) || !is_array($pings)) {
            return response()->json(['status' => 'ok', 'mis' => 100.0]);
        }

        /** @var \App\Services\FraudDetectionService $fraudService */
        $fraudService = app(\App\Services\FraudDetectionService::class);

        $mis    = $fraudService->computeMIS($provider, $pings);
        $status = $fraudService->getStatus($provider);

        // [V2.3] Synchroniser les dernières coordonnées GPS et calculer le geohash du chauffeur
        if (!empty($pings)) {
            $lastPing = end($pings);
            if (isset($lastPing['lat']) && isset($lastPing['lng'])) {
                $provider->latitude  = (float) $lastPing['lat'];
                $provider->longitude = (float) $lastPing['lng'];
                
                // Calculer le geohash de la position courante
                /** @var \App\Services\DispatchEngine\GeoService $geoService */
                $geoService = app(\App\Services\DispatchEngine\GeoService::class);
                $provider->geohash = $geoService->encode((float)$lastPing['lat'], (float)$lastPing['lng'], 5);
                
                $cacheKey = "provider_location_db_write:{$provider->id}";
                if (!\Cache::has($cacheKey)) {
                    $provider->save();
                    \Cache::put($cacheKey, true, now()->addMinutes(2)); // Write at most every 2 min
                }
            }
        }

        return response()->json([
            'status' => $status,
            'mis'    => $mis,
        ]);
    }
}
