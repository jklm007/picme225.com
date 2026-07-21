<?php

namespace App\Http\Controllers\ProviderResources;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use Auth;
use Log;
use Setting;
use Carbon\Carbon;
use App\Helpers\Helper;
use App\Http\Controllers\SendPushNotification;

use App\Models\User;
use App\Models\Admin;
use App\Models\Promocode;
use App\Models\Provider;
use App\Models\AppNotification;
use App\Models\UserRequests;
use App\Models\RequestFilter;
use App\Models\PromocodeUsage;
use App\Models\PromocodePassbook;
use App\Models\ProviderService;
use App\Models\UserRequestRating;
use App\Models\UserRequestPayment;
use App\Models\ServiceType;
use App\Models\WalletPassbook;
use Location\Coordinate;
use Location\Distance\Vincenty;

class TripController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            if ($request->ajax()) {
                $Provider = Auth::user();
            } else {
                $Provider = Auth::guard('providerapi')->user();
            }

            $provider = $Provider->id;

            $AfterAssignProvider = RequestFilter::with(['request.user', 'request.payment', 'request', 'request.service_type'])
                ->where('provider_id', $provider)
                ->whereHas('request', function ($query) use ($provider) {
                    $query->where('status', '<>', 'CANCELLED');
                    $query->where('status', '<>', 'SCHEDULED');
                    $query->where('provider_id', $provider);
                    $query->where('current_provider_id', $provider);
                });

            if (Setting::get('broadcast_request', 0) == 1) {
                $BeforeAssignProvider = RequestFilter::with(['request.user', 'request.payment', 'request', 'request.service_type'])
                    ->where('provider_id', $provider)
                    ->whereHas('request', function ($query) use ($provider) {
                        $query->where('status', '<>', 'CANCELLED');
                        $query->where('status', '<>', 'SCHEDULED');
                        $query->where('current_provider_id', 0);
                    });
            } else {
                $BeforeAssignProvider = RequestFilter::with(['request.user', 'request.payment', 'request', 'request.service_type'])
                    ->where('provider_id', $provider)
                    ->whereHas('request', function ($query) use ($provider) {
                        $query->where('status', '<>', 'CANCELLED');
                        $query->where('status', '<>', 'SCHEDULED');
                        $query->where('current_provider_id', $provider);
                    });
            }

            $IncomingRequests = $BeforeAssignProvider->union($AfterAssignProvider)->get();

            if (!empty($request->latitude)) {
                $Provider->update([
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                ]);
            }

            if (Setting::get('manual_request', 0) == 0) {

                $Timeout = Setting::get('provider_select_timeout', 180);
                if (!empty($IncomingRequests)) {
                    for ($i = 0; $i < sizeof($IncomingRequests); $i++) {
                        $IncomingRequests[$i]->time_left_to_respond = $Timeout - (time() - strtotime($IncomingRequests[$i]->request->assigned_at));
                        if ($IncomingRequests[$i]->request->status == 'SEARCHING' && $IncomingRequests[$i]->time_left_to_respond < 0) {
                            if (Setting::get('broadcast_request', 0) == 1) {
                                $this->assign_destroy($IncomingRequests[$i]->request->id);
                            } else {
                                $this->assign_next_provider($IncomingRequests[$i]->request->id);
                            }
                        }
                    }
                }

            }


            $Response = [
                'account_status' => $Provider->status,
                'service_status' => $Provider->service ? $Provider->service->status : 'offline',
                'requests' => $IncomingRequests,
                'beta_mode' => Setting::get('beta_mode', 0),
            ];

            return $Response;
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Something went wrong']);
        }
    }

    /**
     * Calculate distance between two coordinates.
     * 
     * @return \Illuminate\Http\Response
     */

    public function calculate_distance(Request $request, $id)
    {
        $this->validate($request, [
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric'
        ]);
        try {

            if ($request->ajax()) {
                $Provider = Auth::user();
            } else {
                $Provider = Auth::guard('provider')->user();
            }

            $UserRequest = UserRequests::where('status', 'PICKEDUP')
                ->where('provider_id', $Provider->id)
                ->find($id);

            if ($UserRequest && ($request->latitude && $request->longitude)) {

                Log::info("REQUEST ID:" . $UserRequest->id . "==SOURCE LATITUDE:" . $UserRequest->track_latitude . "==SOURCE LONGITUDE:" . $UserRequest->track_longitude);

                if ($UserRequest->track_latitude && $UserRequest->track_longitude) {

                    $coordinate1 = new Coordinate($UserRequest->track_latitude, $UserRequest->track_longitude); /** Set Distance Calculation Source Coordinates ****/
                    $coordinate2 = new Coordinate($request->latitude, $request->longitude); /** Set Distance calculation Destination Coordinates ****/

                    $calculator = new Vincenty();

                    /***Distance between two coordinates using spherical algorithm (library as mjaschen/phpgeo) ***/

                    $mydistance = $calculator->getDistance($coordinate1, $coordinate2);

                    $meters = round($mydistance);

                    Log::info("REQUEST ID:" . $UserRequest->id . "==BETWEEN TWO COORDINATES DISTANCE:" . $meters . " (m)");

                    if ($meters >= 100) {
                        /*** If traveled distance riched houndred meters means to be the source coordinates ***/
                        $traveldistance = round(($meters / 1000), 8);

                        $calulatedistance = $UserRequest->track_distance + $traveldistance;

                        $UserRequest->track_distance = $calulatedistance;
                        $UserRequest->distance = $calulatedistance;
                        $UserRequest->track_latitude = $request->latitude;
                        $UserRequest->track_longitude = $request->longitude;
                        $UserRequest->save();
                    }
                } else if (!$UserRequest->track_latitude && !$UserRequest->track_longitude) {
                    $UserRequest->distance = 0;
                    $UserRequest->track_latitude = $request->latitude;
                    $UserRequest->track_longitude = $request->longitude;
                    $UserRequest->save();
                }
            }
            return $UserRequest;
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Something went wrong']);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $UserRequest = UserRequests::with(['user', 'payment', 'service_type'])
                ->findOrFail($id);

            return $UserRequest;
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'No trip found!'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong!'], 500);
        }
    }

    /**
     * Abandon volontaire d'une course planifiée par le chauffeur.
     * Libère la course (statut -> SEARCHING) et déclenche la réassignation
     * au conducteur disponible le plus proche.
     *
     * POST /api/provider/trip/{id}/decline-scheduled
     */
    public function declineScheduled(Request $request, $id)
    {
        try {
            $provider  = Auth::user();
            $UserRequest = UserRequests::where('id', $id)
                ->where('provider_id', $provider->id)
                ->where('status', 'SCHEDULED')
                ->firstOrFail();

            // 1. Notifier le chauffeur dans son Centre d'Activités
            AppNotification::send(
                $provider,
                '\u274c Course libérée',
                'Vous avez indiqué ne pas pouvoir assurer la course vers ' . ($UserRequest->d_address ?? 'destination') . '. Un autre conducteur est en cours de recherche.',
                'TRIP_CANCELLED',
                (string) $UserRequest->id,
                'TRIP'
            );

            // 2. Penalite légère (moins sévère qu'une annulation normale)
            $provider->decrement('priority', 15);

            // 3. Libérer le chauffeur
            ProviderService::where('provider_id', $provider->id)->update(['status' => 'active']);

            // 4. Calcul du rayon dynamique selon le temps restant
            $scheduleAt = Carbon::parse($UserRequest->schedule_at);
            $now = Carbon::now();
            if ($now->greaterThanOrEqualTo($scheduleAt)) {
                $radius = 5;
            } else {
                $minutesLeft = $now->diffInMinutes($scheduleAt);
                if ($minutesLeft > 60) {
                    $radius = 15;
                } elseif ($minutesLeft > 30) {
                    $radius = 10;
                } else {
                    $radius = 5;
                }
            }

            // Chercher le conducteur disponible le plus proche
            $nearestProvider = Provider::where('status', 'approved')
                ->where('is_online', 1)
                ->where('is_available', 1)
                ->where('id', '!=', $provider->id)
                ->whereHas('service', function ($q) use ($UserRequest) {
                    $q->where('service_type_id', $UserRequest->service_type_id);
                })
                ->select(\DB::raw("
                    *,
                    (6371 * acos(
                        cos(radians({$UserRequest->s_latitude})) *
                        cos(radians(latitude)) *
                        cos(radians(longitude) - radians({$UserRequest->s_longitude})) +
                        sin(radians({$UserRequest->s_latitude})) *
                        sin(radians(latitude))
                    )) AS distance
                "))
                ->having('distance', '<', $radius)
                ->orderBy('distance', 'asc')
                ->first();

            if ($nearestProvider) {
                // Assigner le nouveau conducteur
                $UserRequest->provider_id         = $nearestProvider->id;
                $UserRequest->current_provider_id = $nearestProvider->id;
                $UserRequest->save();

                // Push FCM + Centre d'Activités au nouveau conducteur
                (new SendPushNotification)->sendPushToProvider(
                    $nearestProvider->id,
                    'URGENT : Course planifiée assignée. Départ : ' . ($UserRequest->pick_address ?? 'voir app') . ' - ' . Carbon::parse($UserRequest->schedule_at)->format('d/m H:i')
                );
                AppNotification::send(
                    $nearestProvider,
                    '\ud83d\ude95 Course planifiée assignée',
                    'Une course planifiée vous a été assignée vers ' . ($UserRequest->d_address ?? 'destination') . ' - départ le ' . Carbon::parse($UserRequest->schedule_at)->format('d/m/Y \u00e0 H:i') . '.',
                    'SCHEDULED_TRIP',
                    (string) $UserRequest->id,
                    'TRIP'
                );

                // Notifier le passager de la réassignation
                (new SendPushNotification)->sendPushToUser(
                    $UserRequest->user_id,
                    "Votre course planifiée a été réassignée à un nouveau conducteur : " . $nearestProvider->first_name . " " . $nearestProvider->last_name . "."
                );

                Log::info("[DeclineScheduled] Course #{$UserRequest->id} reassignee au provider #{$nearestProvider->id}");

                return response()->json([
                    'message'      => 'Course libérée. Un autre conducteur a été assigné.',
                    'reassigned'   => true,
                    'new_provider' => $nearestProvider->id,
                ]);
            } else {
                // Aucun conducteur dispo : remettre en SEARCHING
                $UserRequest->provider_id         = null;
                $UserRequest->current_provider_id = null;
                $UserRequest->status              = 'SEARCHING';
                $UserRequest->assigned_at         = null;
                $UserRequest->save();

                Log::warning("[DeclineScheduled] Aucun conducteur proche pour course #{$UserRequest->id}. Retour en SEARCHING.");
                Log::warning("[DISPATCHER_ALERT] Chauffeur #{$provider->id} a libéré la course planifiée #{$UserRequest->id} et aucun remplaçant n'a été trouvé.");

                // Notifier le passager de la recherche de remplaçant
                (new SendPushNotification)->sendPushToUser(
                    $UserRequest->user_id,
                    "Le chauffeur de votre course planifiée n'est plus disponible. Nous recherchons activement un remplaçant."
                );

                return response()->json([
                    'message'    => 'Course libérée. Recherche d\'un conducteur en cours.',
                    'reassigned' => false,
                ]);
            }

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Course introuvable ou non autorisée.'], 404);
        } catch (\Exception $e) {
            Log::error('[DeclineScheduled] Erreur : ' . $e->getMessage());
            return response()->json(['error' => 'Erreur serveur.'], 500);
        }
    }

    /**
     * Cancel given request.
     *
     * @return \Illuminate\Http\Response
     */
    public function cancel(Request $request)
    {
        $this->validate($request, [
            'cancel_reason' => 'max:255',
        ]);

        try {

            $UserRequest = UserRequests::findOrFail($request->id);
            $Cancellable = ['SEARCHING', 'ACCEPTED', 'ARRIVED', 'STARTED', 'CREATED', 'SCHEDULED'];

            if (!in_array($UserRequest->status, $Cancellable)) {
                return back()->with(['flash_error' => 'Cannot cancel request at this stage!']);
            }

            $UserRequest->status = "CANCELLED";
            $UserRequest->cancel_reason = $request->cancel_reason;
            $UserRequest->cancelled_by = "PROVIDER";
            $UserRequest->save();

            // REMBOURSEMENT DU SÉQUESTRE LOGISTIQUE (LOCATION)
            // Si le chauffeur annule la course, le client est toujours remboursé
            if ($UserRequest->escrow_fee > 0) {
                $user = \App\Models\User::find($UserRequest->user_id);
                if ($user) {
                    $user->increment('wallet_balance', $UserRequest->escrow_fee);
                    \App\Models\WalletPassbook::create([
                        'user_id' => $user->id,
                        'amount' => $UserRequest->escrow_fee,
                        'status' => 'CREDITED',
                        'via' => 'TRIP_ESCROW_REFUND',
                        'description' => 'Remboursement du séquestre (chauffeur a annulé)',
                    ]);
                }
            }

            // Penalize driver for cancelling after acceptance (Yako Balance)
            $penalty = 50; // Standard fallback
            if (Auth::user()->subscription_level == 'pro') {
                $penalty = 25; // Yako Premium (Reduced penalty)
            }
            Auth::user()->decrement('priority', $penalty);
            Auth::user()->update(['completion_streak' => 0]); // Reset streak on cancellation
            Auth::user()->save();

            RequestFilter::where('request_id', $UserRequest->id)->delete();

            $activeTripsCount = UserRequests::where('provider_id', $UserRequest->provider_id)
                ->whereIn('status', ['SEARCHING', 'ACCEPTED', 'STARTED', 'ARRIVED', 'PICKEDUP', 'DROPPED'])
                ->count();
            if ($activeTripsCount == 0) {
                ProviderService::where('provider_id', $UserRequest->provider_id)->update(['status' => 'active']);
            }

            // Send Push Notification to User
            (new SendPushNotification)->ProviderCancellRide($UserRequest);

            // Persister dans le Centre d'Activites du chauffeur (annulation de sa propre initiative)
            \App\Models\AppNotification::send(
                \App\Models\Provider::find($UserRequest->provider_id),
                '\u274c Course annulee par vous',
                'Vous avez annule la course vers ' . ($UserRequest->d_address ?? 'destination') . '. Votre score de priorite a ete reduit.',
                'TRIP_CANCELLED',
                (string) $UserRequest->id,
                'TRIP'
            );

            return $UserRequest;

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Something went wrong']);
        }


    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function rate(Request $request, $id)
    {

        $this->validate($request, [
            'rating' => 'required|integer|in:1,2,3,4,5',
            'comment' => 'max:255',
        ]);

        try {

            $UserRequest = UserRequests::where('id', $id)
                ->where('status', 'COMPLETED')
                ->firstOrFail();

            if ($UserRequest->rating == null) {
                UserRequestRating::create([
                    'provider_id' => $UserRequest->provider_id,
                    'user_id' => $UserRequest->user_id,
                    'request_id' => $UserRequest->id,
                    'provider_rating' => $request->rating,
                    'provider_comment' => $request->comment,
                ]);
            } else {
                $UserRequest->rating->update([
                    'provider_rating' => $request->rating,
                    'provider_comment' => $request->comment,
                ]);
            }

            $UserRequest->update(['provider_rated' => 1]);

            // Delete from filter so that it doesn't show up in status checks.
            RequestFilter::where('request_id', $id)->delete();

            $activeTripsCount = UserRequests::where('provider_id', $UserRequest->provider_id)
                ->whereIn('status', ['SEARCHING', 'ACCEPTED', 'STARTED', 'ARRIVED', 'PICKEDUP', 'DROPPED'])
                ->count();
            if ($activeTripsCount == 0) {
                ProviderService::where('provider_id', $UserRequest->provider_id)->update(['status' => 'active']);
            }

            // Send Push Notification to Provider 
            $average = UserRequestRating::where('provider_id', $UserRequest->provider_id)->avg('provider_rating');

            $UserRequest->user->update(['rating' => $average]);

            // Priority Credit Reward (+5 for every completion)
            Auth::user()->increment('priority', 5);

            // Streak Logic: +50 credits after 5 consecutive trips
            $provider = Auth::user();
            $provider->increment('completion_streak');
            if ($provider->completion_streak >= 5) {
                $provider->increment('priority', 50);
                $provider->update(['completion_streak' => 0]); // Reset after reward
            }
            $provider->save();

            // [V2.3] Compteur de Fatigue Anti-Monopole (composante A du dispatch)
            // Incrémente le compteur de courses récentes (1h glissante).
            // Le ScoreService lit ce compteur pour réduire le score du chauffeur
            // et laisser les courses aux collègues à proximité.
            $fatigueKey = "recent_trips_{$provider->id}";
            $currentCount = (int) \Illuminate\Support\Facades\Cache::get($fatigueKey, 0);
            \Illuminate\Support\Facades\Cache::put($fatigueKey, $currentCount + 1, now()->addHour());

            return response()->json(['message' => 'Request Completed!']);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Request not yet completed!'], 500);
        }
    }

    /**
     * Get the trip history of the provider
     *
     * @return \Illuminate\Http\Response
     */
    public function scheduled(Request $request)
    {
        try {
            $Jobs = UserRequests::where('provider_id', Auth::user()->id)
                ->where('status', 'SCHEDULED')
                ->with('service_type')
                ->get();

            // Antigravity: Add Community Trip Bookings
            $communityBookings = \App\Models\TripBooking::whereHas('trip', function($q) {
                    $q->where('user_id', Auth::user()->id);
                })
                ->where('status', 'CONFIRMED')
                ->with(['user'])
                ->get();

            foreach ($communityBookings as $booking) {
                $item = new \stdClass();
                $item->id = $booking->id;
                $item->booking_id = "PICKME-" . $booking->id;
                $item->s_address = $booking->trip->source_name;
                $item->d_address = $booking->trip->destination_name;
                $item->s_latitude = $booking->trip->source_lat;
                $item->s_longitude = $booking->trip->source_lng;
                $item->d_latitude = $booking->trip->destination_lat;
                $item->d_longitude = $booking->trip->destination_lng;
                $item->schedule_at = $booking->trip->departure_time;
                $item->is_community = true;
                $item->status = $booking->status;
                $item->payment_mode = $booking->payment_mode;
                
                $item->user = [
                    'first_name' => $booking->user->first_name,
                    'last_name' => $booking->user->last_name,
                    'picture' => $booking->user->picture,
                    'rating' => "4.5",
                    'mobile' => $booking->user->mobile
                ];
                
                $Jobs->push($item);
            }

            if (!empty($Jobs)) {
                foreach ($Jobs as $key => $value) {
                    $centerLat = ($value->s_latitude + $value->d_latitude) / 2;
                    $centerLng = ($value->s_longitude + $value->d_longitude) / 2;
                    $Jobs[$key]->static_map = get_static_map($value->s_latitude, $value->s_longitude, $value->d_latitude, $value->d_longitude, isset($value->route_key) ? $value->route_key : null);
                }
            }

            return $Jobs;

        } catch (Exception $e) {
            return response()->json(['error' => "Something Went Wrong"]);
        }
    }

    /**
     * Get the trip history of the provider
     *
     * @return \Illuminate\Http\Response
     */
    public function history(Request $request)
    {
        if ($request->ajax()) {

            $Jobs = UserRequests::where('provider_id', Auth::user()->id)
                ->whereIn('status', ['COMPLETED', 'CANCELLED'])
                ->orderBy('created_at', 'desc')
                ->with('payment', 'service_type', 'user')
                ->get();

            if (!empty($Jobs)) {
                foreach ($Jobs as $key => $value) {
                    $centerLat = ($value->s_latitude + $value->d_latitude) / 2;
                    $centerLng = ($value->s_longitude + $value->d_longitude) / 2;
                    $Jobs[$key]->static_map = get_static_map($value->s_latitude, $value->s_longitude, $value->d_latitude, $value->d_longitude, isset($value->route_key) ? $value->route_key : null);
                }
            }
            return $Jobs;
        }
        $Jobs = UserRequests::where('provider_id', Auth::guard('provider')->user()->id)->with('user', 'service_type', 'payment', 'rating')->get();
        return view('provider.trip.index', compact('Jobs'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function accept(Request $request, $id)
    {
        try {

            $UserRequest = UserRequests::findOrFail($id);
            if ($UserRequest->status != "SEARCHING") {
                if ($UserRequest->provider_id == Auth::user()->id) {
                    // Double-click de ce chauffeur : on renvoie un succès (code 200) sans le champ 'error'
                    return response()->json([
                        'message' => 'Course déjà acceptée.'
                    ]);
                }
                return response()->json(['error' => 'La course n\'est plus disponible.']);
            }

            $UserRequest->provider_id = Auth::user()->id;

            if (Setting::get('broadcast_request', 0) == 1) {
                $UserRequest->current_provider_id = Auth::user()->id;
            }

            // DÉBIT DU SÉQUESTRE LOGISTIQUE (LOCATION) LORS DE L'ACCEPTATION
            if ($UserRequest->escrow_fee > 0) {
                $user = \App\Models\User::find($UserRequest->user_id);
                if ($user && $user->wallet_balance >= $UserRequest->escrow_fee) {
                    $user->decrement('wallet_balance', $UserRequest->escrow_fee);
                    \App\Models\WalletPassbook::create([
                        'user_id' => $user->id,
                        'amount' => $UserRequest->escrow_fee,
                        'status' => 'DEBITED',
                        'via' => 'TRIP_ESCROW',
                        'description' => 'Séquestre logistique location',
                    ]);
                } else {
                    // Le client a dépensé son argent entre temps
                    $UserRequest->status = 'CANCELLED';
                    $UserRequest->save();
                    RequestFilter::where('request_id', $UserRequest->id)->delete();
                    return response()->json(['error' => 'Le client ne dispose plus des fonds nécessaires pour le séquestre. La réservation a été annulée.']);
                }
            }

            if ($UserRequest->schedule_at != "") {

                $beforeschedule_time = strtotime($UserRequest->schedule_at . "- 1 hour");
                $afterschedule_time = strtotime($UserRequest->schedule_at . "+ 1 hour");

                $CheckScheduling = UserRequests::where('status', 'SCHEDULED')
                    ->where('provider_id', Auth::user()->id)
                    ->whereBetween('schedule_at', [$beforeschedule_time, $afterschedule_time])
                    ->count();

                if ($CheckScheduling > 0) {
                    if ($request->ajax()) {
                        return response()->json(['error' => trans('api.ride.request_already_scheduled')]);
                    } else {
                        return redirect('dashboard')->with('flash_error', 'If the ride is already scheduled then we cannot schedule/request another ride for the after 1 hour or before 1 hour');
                    }
                }

                RequestFilter::where('request_id', $UserRequest->id)->where('provider_id', Auth::user()->id)->update(['status' => 2]);

                $UserRequest->status = "SCHEDULED";
                $UserRequest->save();

                // Persister dans le Centre d'Activités du chauffeur
                $scheduleLabel = \Carbon\Carbon::parse($UserRequest->schedule_at)->format('d/m/Y \u00e0 H:i');
                \App\Models\AppNotification::send(
                    \App\Models\Provider::find($UserRequest->provider_id),
                    '\u23f0 Course planifiée confirmée',
                    'Votre course vers ' . ($UserRequest->d_address ?? 'destination') . ' est planifiée le ' . $scheduleLabel . '. Soyez prêt !',
                    'SCHEDULED_TRIP',
                    (string) $UserRequest->id,
                    'TRIP'
                );

            } else {


                $UserRequest->status = "ACCEPTED";
                $UserRequest->save();


                ProviderService::where('provider_id', $UserRequest->provider_id)->update(['status' => 'riding']);

                $Filters = RequestFilter::where('request_id', $UserRequest->id)->where('provider_id', '!=', Auth::user()->id)->get();
                // dd($Filters->toArray());
                foreach ($Filters as $Filter) {
                    $Filter->delete();
                }
            }

            $UnwantedRequest = RequestFilter::where('request_id', '!=', $UserRequest->id)
                ->where('provider_id', Auth::user()->id)
                ->whereHas('request', function ($query) {
                    $query->where('status', '<>', 'SCHEDULED');
                });

            if ($UnwantedRequest->count() > 0) {
                $UnwantedRequest->delete();
            }

            // Send Push Notification to User
            (new SendPushNotification)->RideAccepted($UserRequest);

            // Reward driver for accepting
            $providerToReward = \App\Models\Provider::find($UserRequest->provider_id);
            if ($providerToReward) {
                $providerToReward->increment('priority', 2);
            }

            // MOTEUR IA : Enregistrement de l'acceptation pour le ScoreService
            (new \App\Services\DispatchEngine\DriverLearningService())->recordAcceptance(Auth::user()->id);

            return $UserRequest->with('user')->get();

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Unable to accept, Please try again later']);
        } catch (Exception $e) {
            return response()->json(['error' => 'Connection Error']);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'status' => 'required|in:ACCEPTED,STARTED,ARRIVED,PICKEDUP,DROPPED,PAYMENT,COMPLETED',
            'otp' => 'required_if:status,PICKEDUP',
        ]);

        try {

            $UserRequest = UserRequests::with('user')->findOrFail($id);

            if ($request->status == 'PICKEDUP') {
                if (Setting::get('ride_otp', 0) == 1) {
                    if ($request->otp != $UserRequest->otp) {
                        return response()->json(['error' => 'Code OTP invalide. Veuillez demander le code au passager.'], 400);
                    }
                }
            }

            if ($request->status == 'DROPPED') {
                // Secure Delivery: Check OTP or scan client PicMe Card
                if ($UserRequest->method === 'delivery' || $UserRequest->ride_variant === 'logistics') {
                    $otpValid = $request->has('otp') && $request->otp == $UserRequest->otp;
                    
                    $cardValid = false;
                    if ($request->has('picme_card_token')) {
                        $recipient = \App\Models\User::find($UserRequest->user_id);
                        if ($recipient && !empty($recipient->picme_card_token) && $recipient->picme_card_token === $request->picme_card_token) {
                            $cardValid = true;
                        }
                    }
                    
                    if (!$otpValid && !$cardValid) {
                        return response()->json(['error' => 'Validation sécurisée échouée. Veuillez saisir le code OTP correct ou scanner la carte PicMe valide du destinataire.'], 400);
                    }
                }
            }

            if ($request->status == 'DROPPED' && $UserRequest->payment_mode != 'CASH') {
                $UserRequest->status = 'COMPLETED';
                //$UserRequest->paid = 1;
            } else if ($request->status == 'COMPLETED' && $UserRequest->payment_mode == 'CASH') {
                $UserRequest->status = $request->status;
                $UserRequest->paid = 1;
                // ProviderService::where('provider_id',$UserRequest->provider_id)->update(['status' =>'active']);
            } else {
                $UserRequest->status = $request->status;
                
                // Proof of Delivery (POD) image handling
                if ($request->hasFile('delivery_image')) {
                    $UserRequest->delivery_image = $request->file('delivery_image')->store('delivery_proofs', 's3');
                }

                if ($request->status == 'ARRIVED') {
                    (new SendPushNotification)->Arrived($UserRequest);
                }
            }

            if ($request->status == 'PICKEDUP') {
                if ($UserRequest->is_track == "YES") {
                    // $UserRequest->distance  = 0; 
                }
                $UserRequest->started_at = Carbon::now();
            }

            $UserRequest->save();

            if ($UserRequest->status == 'COMPLETED') {
                $user = \App\Models\User::find($UserRequest->user_id);
                if ($user) {
                    $cashbackPoints = 0;
                    // Vérifier si l'utilisateur a droit à un cashback (quota ou permanent)
                    if ($user->current_discount_rate > 0 && ($user->discount_trips_remaining > 0 || $user->discount_trips_remaining == -1)) {
                        $payment = \App\Models\UserRequestPayment::where('request_id', $UserRequest->id)->first();
                        if ($payment) {
                            // Calcul du cashback en FCFA (ex: 10% de 1000 = 100)
                            $cashbackAmount = $payment->total * $user->current_discount_rate;
                            // Conversion en points Karma (1 point = 10 FCFA, donc 100 FCFA = 10 pts)
                            $cashbackPoints = $cashbackAmount / 10;
                        }

                        // Consommer un trajet du quota si applicable
                        if ($user->discount_trips_remaining > 0) {
                            $user->discount_trips_remaining -= 1;
                        }
                    }

                    // 🏆 RECOMPENSE : +1 point (bonus course) + points cashback variable
                    $totalReward = 1 + $cashbackPoints;
                    $user->increment('social_points', $totalReward);
                    
                    // Mise à jour du badge et des quotas (important pour détection de passage de grade)
                    $user->syncKarmaBadge();
                }
            }

            if ($request->status == 'DROPPED') {
                if ($UserRequest->is_track == "YES") {
                    $UserRequest->d_latitude = $request->latitude ?: $UserRequest->d_latitude;
                    $UserRequest->d_longitude = $request->longitude ?: $UserRequest->d_longitude;
                    $UserRequest->d_address = $request->address ?: $UserRequest->d_address;
                }
                $UserRequest->finished_at = Carbon::now();
                $StartedDate = date_create($UserRequest->started_at);
                $FinisedDate = Carbon::now();
                $TimeInterval = date_diff($StartedDate, $FinisedDate);
                $MintuesTime = $TimeInterval->i;
                $UserRequest->travel_time = $MintuesTime;
                $UserRequest->save();
                $UserRequest->with('user')->findOrFail($id);
                $UserRequest->invoice = $this->invoice($id);

                (new SendPushNotification)->Dropped($UserRequest);

                Helper::site_sendmail($UserRequest);
            }


            // Send Push Notification to User

            return $UserRequest;

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Unable to update, Please try again later']);
        } catch (Exception $e) {
            return response()->json(['error' => 'Connection Error']);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $UserRequest = UserRequests::find($id);

        if (!$UserRequest) {
            return response()->json(['message' => 'Course déjà traitée ou annulée.'], 200);
        }

        try {
            if (Setting::get('broadcast_request', 0) == 1) {
                $provider = Auth::user() ?: Auth::guard('providerapi')->user();
                if ($provider) {
                    RequestFilter::where('provider_id', $provider->id)
                        ->where('request_id', $UserRequest->id)
                        ->delete();
                }
                
                // Si plus aucun chauffeur n'est dans le RequestFilter, on annule la course
                $remainingProviders = RequestFilter::where('request_id', $UserRequest->id)->count();
                if ($remainingProviders == 0) {
                    $UserRequest->status = 'CANCELLED';
                    $UserRequest->save();
                    (new SendPushNotification)->ProviderNotAvailable($UserRequest->user_id);
                }
            } else {
                $this->assign_next_provider($UserRequest->id);
            }

            // MOTEUR IA : Enregistrement du refus pour pénaliser le ScoreService
            $provider = Auth::user() ?: Auth::guard('providerapi')->user();
            if ($provider) {
                (new \App\Services\DispatchEngine\DriverLearningService())->recordRejection($provider->id);
            }

            return response()->json(['message' => 'Course rejetée avec succès.']);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Unable to reject, Please try again later']);
        } catch (Exception $e) {
            return response()->json(['error' => 'Connection Error']);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function assign_destroy($id)
    {
        $UserRequest = UserRequests::find($id);
        try {
            UserRequests::where('id', $UserRequest->id)->update(['status' => 'CANCELLED']);
            
            // No longer need request specific rows from RequestMeta
            RequestFilter::where('request_id', $UserRequest->id)->delete();
            //  request push to user provider not available
            (new SendPushNotification)->ProviderNotAvailable($UserRequest->user_id);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Unable to reject, Please try again later']);
        } catch (Exception $e) {
            return response()->json(['error' => 'Connection Error']);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function assign_next_provider($request_id)
    {

        try {
            $UserRequest = UserRequests::findOrFail($request_id);
        } catch (ModelNotFoundException $e) {
            // Cancelled between update.
            return false;
        }

        $RequestFilter = RequestFilter::where('provider_id', $UserRequest->current_provider_id)
            ->where('request_id', $UserRequest->id)
            ->delete();

        // Penalize provider for timeout/ignoring
        if ($UserRequest->current_provider_id != 0) {
            $provider = \App\Models\Provider::find($UserRequest->current_provider_id);
            if ($provider) {
                $provider->decrement('priority', 1);
            }
        }

        try {

            $next_provider = RequestFilter::where('request_id', $UserRequest->id)
                ->orderBy('id')
                ->firstOrFail();

            $UserRequest->current_provider_id = $next_provider->provider_id;
            $UserRequest->assigned_at = Carbon::now();
            $UserRequest->save();

            // incoming request push to provider
            (new SendPushNotification)->IncomingRequest($next_provider->provider_id, $UserRequest);

            // SMS fallback for offline drivers
            try {
                app(\App\Services\OfflineSmsDispatchService::class)->dispatchToOfflineProviders([$next_provider->provider_id], $UserRequest);
            } catch (\Exception $e) {
                \Log::error("Error dispatching offline SMS in assign_next_provider: " . $e->getMessage());
            }

        } catch (ModelNotFoundException $e) {

            UserRequests::where('id', $UserRequest->id)->update(['status' => 'CANCELLED']);

            // No longer need request specific rows from RequestMeta
            RequestFilter::where('request_id', $UserRequest->id)->delete();

            //  request push to user provider not available
            (new SendPushNotification)->ProviderNotAvailable($UserRequest->user_id);
        }
    }

    public function invoice($request_id)
    {

        try {
            $user = UserRequests::findOrFail($request_id);
            /* $UserRequest = UserRequests::with('package_ren','package')
                       ->whereHas('package_ren', function($query) use ($user){
                               $query->where('service_type_id',$user->service_type_id);
                           })->findOrFail($request_id); */
            $UserRequest = UserRequests::with([
                'package',
                'packagePrice' => function ($query) use ($user) {
                    $query->where('service_type_id', $user->service_type_id);
                },
                'package_ren' => function ($query) use ($user) {
                    $query->where('service_type_id', $user->service_type_id);
                }
            ])->findOrFail($request_id);
            $tax_percentage = Setting::get('tax_percentage');
            $commission_percentage = Setting::get('commission_percentage');

            // DAO Governance: Dynamic Commission based on Subscription
            $daoService = new \App\Services\DaoDistributionService();
            $provider = \App\Models\Provider::find($UserRequest->provider_id);
            $provider_commission_percentage = $daoService->getProviderCommissionRate($provider);

            $service_type = ServiceType::findOrFail($UserRequest->service_type_id);


            $kilo = $UserRequest->distance;
            $reduce_kilometer = $service_type->distance;
            $kilometer = $kilo - $reduce_kilometer;

            if ($kilometer < 0) {
                $kilometer = 0.00;
            }

            $minutes = $UserRequest->travel_time;

            $hour_min = $minutes / 60;
            //$hour_min = 1.25;
            $hour = ceil($hour_min);


            $Fixed = $service_type->fixed;
            $Distance = 0;
            $Discount = 0; // Promo Code discounts should be added here.
            $Wallet = 0;
            $Surge = 0;
            $ProviderCommission = 0;
            $ProviderPay = 0;
            $start_time = new Carbon($UserRequest->started_at);
            $end_time = new Carbon($UserRequest->finished_at);
            $value = $end_time->diffInMinutes($start_time) / 60;
            $value = ceil($value);
            if (isset($UserRequest->package)) {
                $hour_val = $UserRequest->package->hour;
            } else {
                $hour_val = 0;
            }
            $daoService = new \App\Services\DaoDistributionService();
            $p_service_id = $daoService->getServiceIdFromRequest($UserRequest);

            $segmentPriceApplied = false;

            // ---------------------------------------------------------------
            // LOGIQUE DE SEGMENTATION (variantes arret_pdp / arret / arret_hybride)
            // ---------------------------------------------------------------
            $isArretVariant = in_array($UserRequest->ride_variant, ['arret', 'arret_pdp']);
            $isHybrideVariant = ($UserRequest->ride_variant === 'arret_hybride');

            // --- CAS 1 : arret_pdp — Gare à Gare sur ligne fixe ---
            if ($isArretVariant && $UserRequest->pickup_stop_id && $UserRequest->dropoff_stop_id) {
                $startStopObj = \App\Models\PdpStop::find($UserRequest->pickup_stop_id);
                $endStopObj = \App\Models\PdpStop::find($UserRequest->dropoff_stop_id);

                if ($startStopObj && $endStopObj && $startStopObj->pdp_route_id && $startStopObj->pdp_route_id === $endStopObj->pdp_route_id) {
                    $minOrder = min($startStopObj->order, $endStopObj->order);
                    $maxOrder = max($startStopObj->order, $endStopObj->order);

                    $segments = \App\Models\PdpRouteSegment::where('pdp_route_id', $startStopObj->pdp_route_id)
                        ->where('is_active', true)
                        ->where('order', '>=', $minOrder)
                        ->where('order', '<', $maxOrder)
                        ->get();

                    if ($segments->isNotEmpty()) {
                        $Fixed = 0;
                        $Distance = 0;
                        foreach ($segments as $segment) {
                            $Distance += (float) $segment->price;
                        }
                        $segmentPriceApplied = true;

                        $Commision = $Distance * ($commission_percentage / 100);
                        $Tax = ($service_type->is_taxable ?? true) ? $Distance * ($tax_percentage / 100) : 0;

                        $p_commission_data = $daoService->getProviderCommission($provider, $Distance, $p_service_id);
                        $ProviderCommission = $p_commission_data['amount'];
                        $ProviderPay = $Distance - $ProviderCommission;

                        Log::info('arret_pdp: Segmentation pricing applied', ['price' => $Distance, 'request_id' => $UserRequest->id]);
                    }
                }
            }

            // --- CAS 2 : arret_hybride — Arrêt fixe + Destination libre (Dernier Kilomètre) ---
            if ($isHybrideVariant && $UserRequest->pickup_stop_id) {
                $startStopObj = \App\Models\PdpStop::find($UserRequest->pickup_stop_id);

                if ($startStopObj) {
                    // Trouver les routes contenant l'arrêt de départ
                    $destLat = (float) $UserRequest->d_latitude;
                    $destLng = (float) $UserRequest->d_longitude;

                    $startRoutes = \DB::table('pdp_route_stops')
                        ->where('pdp_stop_id', $startStopObj->id)
                        ->get();

                    $bestExitStop = null;
                    $bestRouteId = null;
                    $bestLastKmDistance = null;
                    $bestExitStopOrder = null;
                    $bestStartStopOrder = null;

                    foreach ($startRoutes as $startRoute) {
                        $routeId = $startRoute->pdp_route_id;
                        $startOrder = $startRoute->order;

                        // Trouver l'arrêt après le départ sur cette route qui est le plus proche de la destination
                        $exitStop = \App\Models\PdpStop::join('pdp_route_stops as prs', 'prs.pdp_stop_id', '=', 'pdp_stops.id')
                            ->where('prs.pdp_route_id', $routeId)
                            ->where('prs.order', '>', $startOrder)
                            ->where('pdp_stops.is_active', true)
                            ->selectRaw("pdp_stops.*, prs.order as prs_order, (6371 * acos(
                                cos(radians($destLat)) * cos(radians(latitude)) *
                                cos(radians(longitude) - radians($destLng)) +
                                sin(radians($destLat)) * sin(radians(latitude))
                            )) AS dist_to_dest")
                            ->orderBy('dist_to_dest', 'asc')
                            ->first();

                        if ($exitStop) {
                            $exitLat = (float) $exitStop->latitude;
                            $exitLng = (float) $exitStop->longitude;
                            $lastKmDistance = 6371 * acos(
                                cos(deg2rad($exitLat)) * cos(deg2rad($destLat)) *
                                cos(deg2rad($destLng) - deg2rad($exitLng)) +
                                sin(deg2rad($exitLat)) * sin(deg2rad($destLat))
                            );

                            if ($bestLastKmDistance === null || $lastKmDistance < $bestLastKmDistance) {
                                $bestLastKmDistance = $lastKmDistance;
                                $bestExitStop = $exitStop;
                                $bestRouteId = $routeId;
                                $bestExitStopOrder = $exitStop->prs_order;
                                $bestStartStopOrder = $startOrder;
                            }
                        }
                    }

                    if ($bestExitStop) {
                        // Partie 1 : Prix des segments de la ligne (départ → arrêt de sortie)
                        $minOrder = min($bestStartStopOrder, $bestExitStopOrder);
                        $maxOrder = max($bestStartStopOrder, $bestExitStopOrder);

                        $segments = \App\Models\PdpRouteSegment::where('pdp_route_id', $bestRouteId)
                            ->where('is_active', true)
                            ->where('order', '>=', $minOrder)
                            ->where('order', '<', $maxOrder)
                            ->get();

                        $segmentTotal = 0;
                        foreach ($segments as $segment) {
                            $segmentTotal += (float) $segment->price;
                        }

                        // Partie 2 : Prix kilométrique de l'arrêt de sortie vers la destination libre
                        // Tarif km configurable via le service type, sinon fallback au prix du service
                        $pricePerKm = (float) ($service_type->price_per_km ?: $service_type->price);
                        $lastKmPrice = $bestLastKmDistance * $pricePerKm;

                        $Distance = $segmentTotal + $lastKmPrice;
                        $Fixed = 0;
                        $segmentPriceApplied = true;

                        $Commision = $Distance * ($commission_percentage / 100);
                        $Tax = ($service_type->is_taxable ?? true) ? $Distance * ($tax_percentage / 100) : 0;

                        $p_commission_data = $daoService->getProviderCommission($provider, $Distance, $p_service_id);
                        $ProviderCommission = $p_commission_data['amount'];
                        $ProviderPay = $Distance - $ProviderCommission;

                        Log::info('arret_hybride: Dernier kilomètre pricing applied', [
                            'request_id'   => $UserRequest->id,
                            'segment_sum'  => $segmentTotal,
                            'exit_stop'    => $bestExitStop->name,
                            'last_km_dist' => round($bestLastKmDistance, 2) . ' km',
                            'last_km_price'=> $lastKmPrice,
                            'total'        => $Distance,
                        ]);
                    }
                }
            }

            if (!$segmentPriceApplied) {
                $variantService = new \App\Services\RideVariantService();

                if ($user->method == "outstation") {
                    if ($user->round_trip == 1) {
                        $Distance = ($kilo * $service_type->outstation_price * 2);
                    } else {
                        $Distance = ($kilo * $service_type->outstation_price);
                    }

                    // Apply Variant Adjustment (Prive/Arret)
                    $Distance = $variantService->applyVariantDiscount($Distance, $UserRequest->ride_variant);

                    $Commision = $Distance * ($commission_percentage / 100);
                    $Tax = ($service_type->is_taxable ?? true) ? $Distance * ($tax_percentage / 100) : 0;

                    $p_commission_data = $daoService->getProviderCommission($provider, $Distance, $p_service_id);
                    $ProviderCommission = $p_commission_data['amount'];
                    $ProviderPay = $Distance - $ProviderCommission;
                } else if (($value > $hour_val) || ($UserRequest->package_id == 0)) {
                    if ($service_type->calculator == 'MIN') {
                        $Distance = $service_type->minute * $minutes;
                    } else if ($service_type->calculator == 'HOUR') {
                        $Distance = $service_type->minute * 60;
                    } else if ($service_type->calculator == 'DISTANCE') {
                        $Distance = ($kilometer * $service_type->price);
                    } else if ($service_type->calculator == 'DISTANCEMIN') {
                        $Distance = ($kilometer * $service_type->price) + ($service_type->minute * $minutes);
                    } else if ($service_type->calculator == 'DISTANCEHOUR') {
                        $Distance = ($kilometer * $service_type->price) + ($service_type->hour * $hour);
                    } else {
                        $Distance = ($kilometer * $service_type->price);
                    }

                    // Apply Variant Adjustment (Prive/Arret)
                    $total_base = $Fixed + $Distance;
                    $total_discounted = $variantService->applyVariantDiscount($total_base, $UserRequest->ride_variant);
                    $Distance = $total_discounted - $Fixed;

                    $Commision = ($Distance + $Fixed) * ($commission_percentage / 100);
                    $Tax = ($service_type->is_taxable ?? true) ? ($Distance + $Fixed) * ($tax_percentage / 100) : 0;

                    $p_commission_data = $daoService->getProviderCommission($provider, $Distance + $Fixed, $p_service_id);
                    $ProviderCommission = $p_commission_data['amount'];
                    $ProviderPay = ($Distance + $Fixed) - $ProviderCommission;
                } else {
                    if ($UserRequest->packagePrice) {
                        $Distance = $UserRequest->packagePrice->price;
                    } elseif (count($UserRequest->package_ren) != 0) {
                        $Distance = $UserRequest->package_ren[0]['ren_price'];
                    } else {
                        $Distance = 0;
                    }

                    // Apply Variant Adjustment (Prive/Arret)
                    $Distance = $variantService->applyVariantDiscount($Distance, $UserRequest->ride_variant);

                    $Commision = $Distance * ($commission_percentage / 100);
                    $Tax = ($service_type->is_taxable ?? true) ? $Distance * ($tax_percentage / 100) : 0;

                    $p_commission_data = $daoService->getProviderCommission($provider, $Distance, $p_service_id);
                    $ProviderCommission = $p_commission_data['amount'];
                    $ProviderPay = $Distance - $ProviderCommission;
                }
            }



            /* } else {
               $start_time =   new Carbon($UserRequest->started_at);
               $end_time =  new Carbon($UserRequest->finished_at);
               $value =  $end_time->diffInMinutes($start_time)/60;
               $value = ceil($value);
               if( $value >= $UserRequest->rental_hour){
                   $Distance = $value * $service_type->rental_amount;
               } else {
                   $Distance = $UserRequest->rental_hour * $service_type->rental_amount;
               }
               $Commision = $Distance  * ( $commission_percentage/100 );
                   $Tax = $Distance  * ( $tax_percentage/100 );
                   $ProviderCommission = $Distance  * ( $provider_commission_percentage/100 );
                   $ProviderPay = $Distance  - $ProviderCommission;*/


            if ($PromocodeUsage = PromocodeUsage::where('user_id', $UserRequest->user_id)->where('status', 'ADDED')->first()) {
                if ($Promocode = Promocode::find($PromocodeUsage->promocode_id)) {
                    $Discount = $Promocode->discount;
                    $PromocodeUsage->status = 'USED';
                    $PromocodeUsage->save();

                    PromocodePassbook::create([
                        'user_id' => Auth::user()->id,
                        'status' => 'USED',
                        'promocode_id' => $PromocodeUsage->promocode_id
                    ]);
                }

                if ($PromocodeUsage->promocode->discount_type == 'amount') {
                    if ($user->method == "outstation") {
                        $Total = $Distance + $Tax - $Discount;
                    } else if ($UserRequest->package_id == 0) {
                        $Total = $Fixed + $Distance + $Tax - $Discount;
                    } else {
                        $Total = $Distance + $Tax - $Discount;
                    }
                } else {
                    if ($user->method == "outstation") {
                        $Discount = ($Distance + $Tax) * ($Discount / 100);
                        $Total = ($Distance + $Tax) - $Discount;
                    } else if ($UserRequest->package_id == 0) {
                        $Discount = ($Fixed + $Distance + $Tax) * ($Discount / 100);
                        $Total = ($Fixed + $Distance + $Tax) - $Discount;
                    } else {
                        $Discount = ($Distance + $Tax) * ($Discount / 100);
                        $Total = ($Distance + $Tax) - $Discount;
                    }

                    //$Discount = (($Fixed + $Distance + $Tax) * ($Discount/100));

                }

            } else {
                if ($user->method == "outstation") {
                    $Total = $Distance + $Tax - $Discount;
                } else if ($UserRequest->package_id == 0) {
                    $Total = $Fixed + $Distance + $Tax - $Discount;
                } else {
                    $Total = $Distance + $Tax - $Discount;
                }
            }


            if ($UserRequest->surge) {
                $Surge = (Setting::get('surge_percentage') / 100) * $Total;
                $Total += $Surge;
            }

            // 💎 KARMA DISCOUNT (Badges/Rank - Quotas de trajets) - Désormais en Cashback après course
            $karmaDiscountAmount = 0;
            $user = \App\Models\User::find($UserRequest->user_id);
            if ($user && $user->current_discount_rate > 0) {
                if ($user->discount_trips_remaining > 0 || $user->discount_trips_remaining == -1) {
                    $discountRate = $user->current_discount_rate;
                    $karmaDiscountAmount = $Total * $discountRate;
                    // On ne soustrait plus le montant ici (système de cashback après course)
                }
            }

            // 💰 KARMA REDEMPTION (Pay with Points)
            $karmaRedeemAmount = 0;
            if ($UserRequest->use_karma == 1 && $UserRequest->karma_points_used > 0) {
                // Taux: 1 point = 10 FCFA
                $karmaRedeemAmount = $UserRequest->karma_points_used * 10;
                // On s'assure que ça ne dépasse pas le total restant
                if ($karmaRedeemAmount > $Total) {
                    $karmaRedeemAmount = $Total;
                }
                $Total -= $karmaRedeemAmount;
            }

            // Antigravity: Add Booking Fee to Total
            $booking_fee = $UserRequest->booking_fee ?? 0;
            $Total += $booking_fee;

            if ($Total < 0) {
                $Total = 0.00; // prevent from negative value
            }

            // BÊTA MODE AND SIMULATION ADAPTATION
            $beta_mode = Setting::get('beta_mode', '0') === '1';
            $commission_enabled = Setting::get('commission_enabled', '1') === '1';

            if ($beta_mode) {
                // Log simulated values for statistical reporting
                \DB::table('simulated_commission_logs')->insert([
                    'request_id' => $UserRequest->id,
                    'total_amount' => $Total,
                    'simulated_commission' => $ProviderCommission,
                    'simulated_provider_pay' => $ProviderPay,
                    'created_at' => now()
                ]);

                if (!$commission_enabled) {
                    $Commision = 0;
                    $ProviderCommission = 0;
                    $ProviderPay = $Total;
                }
            }

            $Payment = new UserRequestPayment;
            $Payment->request_id = $UserRequest->id;

            /*
             * Reported by Jeya, We are adding the surge price with Base price of Service Type.
             */
            if ($user->method == "outstation") {
                $Payment->fixed = 0;
            } else if ($UserRequest->package_id != 0) {
                $Payment->fixed = $Distance;
            } else {
                $Payment->fixed = $Fixed + $Surge;
            }
            $Payment->distance = $Distance;
            $Payment->commision = $Commision;
            $Payment->surge = $Surge;
            $Payment->booking_fee = $booking_fee;
            $Payment->total = $Total;
            $Payment->provider_commission = $ProviderCommission;
            $Payment->provider_pay = $ProviderPay;
            if ($Discount != 0 && $PromocodeUsage) {
                $Payment->promocode_id = $PromocodeUsage->promocode_id;
            }
            $Payment->discount = $Discount;
            $Payment->karma_discount = $karmaDiscountAmount;
            $Payment->karma_redeem = $karmaRedeemAmount;

            if (($Discount + $karmaDiscountAmount + $karmaRedeemAmount) >= ($Fixed + $Distance + $Tax)) {
                $UserRequest->paid = 1;
            }

            if ($UserRequest->use_wallet == 1 && $Total > 0) {

                $User = User::find($UserRequest->user_id);

                $Wallet = $User->wallet_balance;

                if ($Wallet != 0) {

                    if ($Total > $Wallet) {

                        $Payment->wallet = $Wallet;
                        $Payable = $Total - $Wallet;
                        User::where('id', $UserRequest->user_id)->update(['wallet_balance' => 0]);
                        $Payment->payable = abs($Payable);

                        WalletPassbook::create([
                            'user_id' => $UserRequest->user_id,
                            'amount' => $Wallet,
                            'status' => 'DEBITED',
                            'via' => 'TRIP',
                        ]);

                        // charged wallet money push 
                        (new SendPushNotification)->ChargedWalletMoney($UserRequest->user_id, currency($Wallet));

                    } else {

                        $Payment->payable = 0;
                        $WalletBalance = $Wallet - $Total;
                        User::where('id', $UserRequest->user_id)->update(['wallet_balance' => $WalletBalance]);
                        $Payment->wallet = $Total;

                        $Payment->payment_id = 'WALLET';
                        $Payment->payment_mode = $UserRequest->payment_mode;

                        $UserRequest->paid = 1;
                        $UserRequest->status = 'COMPLETED';
                        $UserRequest->save();

                        WalletPassbook::create([
                            'user_id' => $UserRequest->user_id,
                            'amount' => $Total,
                            'status' => 'DEBITED',
                            'via' => 'TRIP',
                        ]);

                        // charged wallet money push 
                        (new SendPushNotification)->ChargedWalletMoney($UserRequest->user_id, currency($Total));
                    }

                }
            } else {
                $Payment->total = abs($Total);
                $Payment->payable = abs($Total);
            }

            $Payment->tax = $Tax;
            $Payment->save();

            // DAO Distribution Integration
            try {
                $daoService = new \App\Services\DaoDistributionService();
                $daoService->applyDaoFees($Payment);
                $daoService->handleCashPayment($Payment);
            } catch (\Exception $e) {
                Log::error("DAO Distribution Error: " . $e->getMessage());
            }

            // ---------------------------------------------------------------
            // ENRICHISSEMENT FACTURE — Date/heure, service type, infos driver
            // ---------------------------------------------------------------
            $providerService = \App\Models\ProviderService::where('provider_id', $UserRequest->provider_id)->first();

            return [
                'payment'        => $Payment,
                'request_id'     => $UserRequest->id,
                'started_at'     => $UserRequest->started_at
                                    ? (string) $UserRequest->started_at
                                    : null,
                'finished_at'    => $UserRequest->finished_at
                                    ? (string) $UserRequest->finished_at
                                    : null,
                'payment_mode'   => $UserRequest->payment_mode,
                'service_type'   => [
                    'id'   => $service_type->id,
                    'name' => $service_type->name ?? '',
                ],
                'driver' => [
                    'id'             => $provider ? $provider->id : null,
                    'name'           => $provider
                                        ? trim($provider->first_name . ' ' . $provider->last_name)
                                        : '',
                    'vehicle_number' => $providerService ? ($providerService->vehicle_number ?? '') : '',
                    'vehicle_model'  => $providerService ? ($providerService->vehicle_model  ?? '') : '',
                    'vehicle_make'   => $providerService ? ($providerService->vehicle_make   ?? '') : '',
                    'avatar'         => $provider ? ($provider->avatar ?? '') : '',
                ],
            ];

        } catch (ModelNotFoundException $e) {
            Log::error('Invoice generation failed for request #' . $request_id . ': ' . $e->getMessage());
            return false;
        } catch (\Exception $e) {
            Log::error('Invoice unexpected error for request #' . $request_id . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get the trip history details of the provider
     *
     * @return \Illuminate\Http\Response
     */
    public function history_details(Request $request)
    {
        //        dd($request);
        $this->validate($request, [
            'request_id' => 'required|integer|exists:user_requests,id',
        ]);

        if ($request->ajax()) {

            $Jobs = UserRequests::where('id', $request->request_id)
                ->where('provider_id', Auth::user()->id)
                ->with('payment', 'service_type', 'user', 'rating')
                ->get();
            if (!empty($Jobs)) {
                foreach ($Jobs as $key => $value) {
                    $centerLat = ($value->s_latitude + $value->d_latitude) / 2;
                    $centerLng = ($value->s_longitude + $value->d_longitude) / 2;
                    $Jobs[$key]->static_map = get_static_map($value->s_latitude, $value->s_longitude, $value->d_latitude, $value->d_longitude, isset($value->route_key) ? $value->route_key : null);
                }
            }

            return $Jobs;
        }

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function upcoming_trips()
    {

        try {
            $UserRequests = UserRequests::ProviderUpcomingRequest(Auth::user()->id)->get();
            if (!empty($UserRequests)) {
                foreach ($UserRequests as $key => $value) {
                    $centerLat = ($value->s_latitude + $value->d_latitude) / 2;
                    $centerLng = ($value->s_longitude + $value->d_longitude) / 2;
                    $UserRequests[$key]->static_map = get_static_map($value->s_latitude, $value->s_longitude, $value->d_latitude, $value->d_longitude, isset($value->route_key) ? $value->route_key : null);
                }
            }
            return $UserRequests;
        } catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')]);
        }
    }

    /**
     * Get the trip history details of the provider
     *
     * @return \Illuminate\Http\Response
     */
    public function upcoming_details(Request $request)
    {
        $this->validate($request, [
            'request_id' => 'required|integer|exists:user_requests,id',
        ]);

        if ($request->ajax()) {

            $Jobs = UserRequests::where('id', $request->request_id)
                ->where('provider_id', Auth::user()->id)
                ->with('service_type', 'user')
                ->get();
            if (!empty($Jobs)) {
                foreach ($Jobs as $key => $value) {
                    $centerLat = ($value->s_latitude + $value->d_latitude) / 2;
                    $centerLng = ($value->s_longitude + $value->d_longitude) / 2;
                    $Jobs[$key]->static_map = get_static_map($value->s_latitude, $value->s_longitude, $value->d_latitude, $value->d_longitude, isset($value->route_key) ? $value->route_key : null);
                }
            }

            return $Jobs;
        }

    }

    /**
     * Get the trip history details of the provider
     *
     * @return \Illuminate\Http\Response
     */
    public function summary(Request $request)
    {
        try {
            $providerId = Auth::user()->id;
            $period = $request->input('period', 'today');

            // Build date filter
            $query = UserRequests::where('provider_id', $providerId);
            if ($period === 'week') {
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
            } elseif ($period === 'month') {
                $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
            } elseif ($period === 'year') {
                $query->whereYear('created_at', now()->year);
            } elseif ($period === 'all') {
                // No time filter for 'all'
            } else {
                $query->whereDate('created_at', today());
            }

            $ridesTotal = (clone $query)->where('status', 'COMPLETED')->count();

            $revenue_total = (clone $query)->with('payment')->get()->sum(function ($req) {
                return $req->payment ? $req->payment->provider_pay : 0;
            });

            $revenue = $revenue_total;

            $cancel_rides = (clone $query)->where('status', 'CANCELLED')->count();
            $scheduled_rides = UserRequests::where('provider_id', $providerId)
                ->where('status', 'SCHEDULED')
                ->count();

            // Chart data - last 7 days
            $chartData = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $dayRevenue = UserRequests::where('provider_id', $providerId)
                    ->whereIn('status', ['COMPLETED', 'CANCELLED'])
                    ->whereDate('created_at', $date->toDateString())
                    ->with('payment')
                    ->get()
                    ->sum(function ($req) {
                        return $req->payment ? $req->payment->provider_pay : 0;
                    });
                $dayRides = UserRequests::where('provider_id', $providerId)
                    ->where('status', 'COMPLETED')
                    ->whereDate('created_at', $date->toDateString())
                    ->count();
                $chartData[] = [
                    'label' => $date->format('D'),
                    'revenue' => (float) $dayRevenue,
                    'rides' => (int) $dayRides,
                ];
            }

            return response()->json([
                'rides' => $ridesTotal,
                'revenue' => $revenue,
                'cancel_rides' => $cancel_rides,
                'scheduled_rides' => $scheduled_rides,
                'wallet_balance' => Auth::user()->eco_wallet_balance ?? 0,
                'priority' => Auth::user()->priority ?? 0,
                'chart_data' => $chartData,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }



    /**
     * help Details.
     *
     * @return \Illuminate\Http\Response
     */

    public function help_details(Request $request)
    {
        try {
            return response()->json([
                'contact_number' => Setting::get('contact_number', ''),
                'contact_email' => Setting::get('contact_email', '')
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')], 500);
        }
    }

    /**
     * Reçoit le message d'assistance d'un chauffeur, l'enregistre sur Firebase,
     * puis génère et enregistre une réponse d'IA (GROQ) de manière synchrone.
     */
    public function sendSupportMessage(Request $request)
    {
        $this->validate($request, [
            'message' => 'required|string|max:1000',
        ]);

        try {
            $provider = Auth::guard('providerapi')->user();
            if (!$provider) {
                $provider = Auth::user();
            }

            if (!$provider) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }

            $providerId = $provider->id;
            $driverName = $provider->first_name . ' ' . $provider->last_name;
            $driverMsgText = $request->message;

            // 1. Sauvegarder le message du chauffeur en SQL
            $msg = \App\Models\SupportMessage::create([
                'provider_id' => $providerId,
                'sender' => 'driver',
                'message' => $driverMsgText,
            ]);

            // Diffuser le message via WebSockets
            try {
                broadcast(new \App\Events\NewSupportMessage($msg));
            } catch (\Exception $e) { }

            // 2. Générer la réponse de support IA
            $ai = new \App\Services\PicmeAiService();
            $supportReply = $ai->generateSupportReply($driverMsgText, $driverName);

            if ($supportReply) {
                // 3. Sauvegarder la réponse d'IA en SQL
                $aiMsg = \App\Models\SupportMessage::create([
                    'provider_id' => $providerId,
                    'sender' => 'agent_picme_ai',
                    'message' => $supportReply,
                ]);

                // Diffuser la réponse de l'IA via WebSockets
                try {
                    broadcast(new \App\Events\NewSupportMessage($aiMsg));
                } catch (\Exception $e) { }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Message et réponse IA enregistrés (SQL).',
                'ai_reply' => $supportReply
            ]);

        } catch (\Exception $e) {
            \Log::error('Support Chat Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to process support message: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Récupère l'historique des messages de support (SQL)
     */
    public function getSupportHistory(Request $request)
    {
        try {
            $provider = Auth::guard('providerapi')->user();
            if (!$provider) {
                $provider = Auth::user();
            }

            if (!$provider) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }

            $messages = \App\Models\SupportMessage::where('provider_id', $provider->id)
                ->orderBy('created_at', 'asc')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $messages
            ]);
        } catch (\Exception $e) {
            \Log::error('Support History Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch support history: ' . $e->getMessage()], 500);
        }
    }

}
