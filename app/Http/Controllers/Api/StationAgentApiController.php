<?php

namespace App\Http\Controllers\Api;

use App\Models\PackageRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserRequests;
use App\Models\Provider;
use App\Models\ServiceType;
use App\Models\Partner;
use App\Models\WalletPassbook;
use Setting;
use App\Models\RequestFilter;
use App\Helpers\Helper;
use Auth;
use Log;
use DB;



class StationAgentApiController extends Controller
{
    /**
     * Resolve the Partner record for the authenticated user (station-agent type).
     * Returns null if not yet migrated (legacy fallback will be used).
     */
    private function resolvePartner(): ?Partner
    {
        $user = Auth::user();
        if (!$user) return null;

        return Partner::where('user_id', $user->id)
            ->where('type', 'STATION_AGENT')
            ->first();
    }

    /**
     * Credit commission to a partner user wallet (new system) or legacy agent wallet.
     */
    private function creditCommission(Partner $partner, float $amount, string $via, string $description, string $referenceId): void
    {
        $partnerUser = $partner->user;
        if (!$partnerUser) return;

        $partnerUser->increment('wallet_balance', $amount);

        WalletPassbook::create([
            'user_id'      => $partnerUser->id,
            'partner_id'   => $partner->id,
            'amount'       => $amount,
            'status'       => 'CREDITED',
            'via'          => $via,
            'description'  => $description,
            'reference_id' => $referenceId,
        ]);
    }

    /**
     * List packages at the agent's station.
     */
    public function packages(Request $request)
    {
        $agent = Auth::user()->stationAgent;
        if (!$agent) {
            return response()->json(['error' => 'Non autorisé. Vous n\'êtes pas un agent de gare.'], 403);
        }

        $stationId = $agent->pdp_stop_id;

        $incoming = PackageRequest::where('dropoff_station_id', $stationId)
            ->whereIn('status', ['IN_TRANSIT', 'ARRIVED'])
            ->with('user')
            ->get();

        $outgoing = PackageRequest::where('pickup_station_id', $stationId)
            ->whereIn('status', ['CREATED', 'DEPOSITED'])
            ->with('user')
            ->get();

        return response()->json([
            'incoming' => $incoming,
            'outgoing' => $outgoing
        ]);
    }

    /**
     * Process a package (Scan action).
     */
    public function process(Request $request)
    {
        $this->validate($request, [
            'tracking_code' => 'required',
            'action' => 'required|in:RECEIVE_FROM_CUSTOMER,SEND_TO_DESTINATION,RECEIVE_FROM_BUS,DELIVER_TO_RECIPIENT',
            'otp' => 'required_if:action,DELIVER_TO_RECIPIENT'
        ]);

        // Résolution du partenaire (nouveau système) ou agent legacy
        $partner = $this->resolvePartner();
        $agent   = Auth::user()->stationAgent;

        if (!$partner && !$agent) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $package = PackageRequest::where('tracking_code', $request->tracking_code)->firstOrFail();

            switch ($request->action) {
                case 'RECEIVE_FROM_CUSTOMER':
                    $package->status = 'DEPOSITED';
                    $msg = "Colis réceptionné en gare.";

                    if ($partner) {
                        $comm = (float) $partner->getCommissionRule('parcel_receive_cfa', 150);
                        $this->creditCommission(
                            $partner, $comm, 'PARCEL_RECEIVE',
                            "Commission réception colis (Code #{$package->tracking_code})",
                            (string) $package->id
                        );
                    } elseif ($agent) {
                        $comm = $agent->commission_per_parcel;
                        $agent->increment('wallet_balance', $comm);
                        DB::table('agent_commission_logs')->insert([
                            'station_agent_id' => $agent->id, 'type' => 'PARCEL_RECEIVE',
                            'amount' => $comm, 'reference_id' => $package->id,
                            'description' => "Commission réception colis (Code #{$package->tracking_code}) [legacy]",
                            'created_at' => now(), 'updated_at' => now(),
                        ]);
                    }

                    if ($package->needs_collection && $package->collection_request_id) {
                        $ride = UserRequests::find($package->collection_request_id);
                        if ($ride && $ride->status != 'COMPLETED') {
                            $ride->status    = 'COMPLETED';
                            $ride->finished_at = now();
                            $ride->save();
                            Log::info("Collection ride {$ride->booking_id} completed via Station Agent scan.");
                        }
                    }
                    break;

                case 'SEND_TO_DESTINATION':
                    $package->status = 'IN_TRANSIT';
                    $msg = "Colis expédié dans le car.";

                    if ($partner) {
                        $comm = (float) $partner->getCommissionRule('parcel_send_cfa', 75);
                        $this->creditCommission(
                            $partner, $comm, 'PARCEL_SEND',
                            "Commission mise en car colis (Code #{$package->tracking_code})",
                            (string) $package->id
                        );
                    } elseif ($agent) {
                        $comm = round($agent->commission_per_parcel / 2, 2);
                        $agent->increment('wallet_balance', $comm);
                        DB::table('agent_commission_logs')->insert([
                            'station_agent_id' => $agent->id, 'type' => 'PARCEL_SEND',
                            'amount' => $comm, 'reference_id' => $package->id,
                            'description' => "Commission mise en car colis (Code #{$package->tracking_code}) [legacy]",
                            'created_at' => now(), 'updated_at' => now(),
                        ]);
                    }
                    break;

                case 'RECEIVE_FROM_BUS':
                    $package->status = 'ARRIVED';
                    $msg = "Colis arrivé à destination.";
                    break;

                case 'DELIVER_TO_RECIPIENT':
                    if ($package->otp_pickup != $request->otp) {
                        return response()->json(['error' => 'Code OTP incorrect.'], 400);
                    }
                    $package->status = 'DELIVERED';
                    $msg = "Colis livré au destinataire.";

                    try {
                        $distributor = new \App\Services\DaoDistributionService();
                        $distributor->distributeLogisticsRevenue($package);
                    } catch (\Exception $e) {
                        Log::error("Logistics Distribution Error: " . $e->getMessage());
                    }
                    break;
            }

            $package->save();

            return response()->json([
                'message' => $msg,
                'package' => $package
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Colis non trouvé ou erreur technique.'], 404);
        }
    }

    /**
     * Get agent summary (balance, daily stats).
     */
    public function summary(Request $request)
    {
        $partner = $this->resolvePartner();
        $agent   = Auth::user()->stationAgent;

        if (!$partner && !$agent) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($partner && $partner->user) {
            // Nouveau système
            $partnerUser = $partner->user;
            $station     = $partner->station ?? ($agent ? $agent->station : null);
            $dailyEarnings = \App\Models\WalletPassbook::where('partner_id', $partner->id)
                ->whereDate('created_at', now()->toDateString())
                ->where('status', 'CREDITED')
                ->sum('amount');
            $dailyScans = \App\Models\WalletPassbook::where('partner_id', $partner->id)
                ->whereDate('created_at', now()->toDateString())
                ->count();

            return response()->json([
                'wallet_balance'           => $partnerUser->wallet_balance,
                'commission_per_passenger' => $partner->getCommissionRule('passenger_scan_cfa', 50),
                'commission_per_parcel'    => $partner->getCommissionRule('parcel_receive_cfa', 150),
                'station_name'             => $station ? $station->name : 'N/A',
                'station_type'             => $station ? $station->type : 'arret',
                'is_company_agent'         => !empty($partner->metadata['interurban_company_id']),
                'daily_scans'              => $dailyScans,
                'daily_earnings'           => $dailyEarnings,
                'system'                   => 'unified',
            ]);
        }

        // Fallback legacy
        $station = $agent->station;
        return response()->json([
            'wallet_balance'           => $agent->wallet_balance,
            'commission_per_passenger' => $agent->commission_per_passenger,
            'commission_per_parcel'    => $agent->commission_per_parcel,
            'station_name'             => $station ? $station->name : 'N/A',
            'station_type'             => $station ? $station->type : 'arret',
            'is_company_agent'         => !empty($agent->interurban_company_id),
            'daily_scans' => DB::table('agent_commission_logs')
                ->where('station_agent_id', $agent->id)
                ->whereDate('created_at', now()->toDateString())
                ->count(),
            'daily_earnings' => DB::table('agent_commission_logs')
                ->where('station_agent_id', $agent->id)
                ->whereDate('created_at', now()->toDateString())
                ->sum('amount'),
            'system' => 'legacy',
        ]);
    }

    /**
     * Request a withdrawal.
     */
    public function withdraw(Request $request)
    {
        $this->validate($request, [
            'amount' => 'required|numeric|min:1000',
            'mobile_money_num' => 'required'
        ]);

        $agent = Auth::user()->stationAgent;
        if (!$agent) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($agent->wallet_balance < $request->amount) {
            return response()->json(['error' => 'Solde insuffisant.'], 400);
        }

        try {
            DB::transaction(function () use ($agent, $request) {
                DB::table('agent_commission_logs')->insert([
                    'station_agent_id' => $agent->id,
                    'type' => 'WITHDRAWAL',
                    'amount' => -$request->amount,
                    'reference_id' => 'WDR_' . time(),
                    'description' => "Demande de retrait vers {$request->mobile_money_num}",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $agent->decrement('wallet_balance', $request->amount);
            });

            return response()->json(['message' => 'Demande de retrait enregistrée et envoyée à votre gestionnaire.']);

        } catch (\Exception $e) {
            Log::error("Agent Withdrawal Error: " . $e->getMessage());
            return response()->json(['error' => 'Erreur technique locale.'], 500);
        }
    }

    // =========================================================================
    // GARE DE COMPAGNIE — Liste des cars de la flotte
    // =========================================================================

    /**
     * Get active rides from the agent's company fleet.
     * Only for agents with interurban_company_id set (Gare).
     */
    public function getActiveRides(Request $request)
    {
        $agent = Auth::user()->stationAgent;
        if (!$agent) return response()->json(['error' => 'Unauthorized'], 403);

        if (!$agent->interurban_company_id) {
            return response()->json(['error' => 'Vous n\'êtes pas un agent de gare de compagnie.'], 403);
        }

        $rides = \App\Models\ActiveSharedRide::where('status', 'EN_ROUTE')
            ->whereHas('provider', function($q) use ($agent) {
                $q->where('fleet', optional($agent->company)->fleet_id);
            })
            ->with(['provider', 'route'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $rides
        ]);
    }

    /**
     * Assign a ride (vehicle) to the agent's station (Mark as Arrived).
     * Only for Gare agents.
     */
    public function assignRideToStation(Request $request)
    {
        $this->validate($request, [
            'ride_id' => 'required|integer|exists:active_shared_rides,id'
        ]);

        $agent = Auth::user()->stationAgent;
        if (!$agent) return response()->json(['error' => 'Unauthorized'], 403);

        $ride = \App\Models\ActiveSharedRide::findOrFail($request->ride_id);

        if ($ride->provider->fleet != optional($agent->company)->fleet_id) {
            return response()->json(['error' => 'Ce véhicule appartient à une autre flotte.'], 403);
        }

        $ride->update([
            'current_stop_id' => $agent->pdp_stop_id,
            'last_position_update' => now()
        ]);

        try {
            broadcast(new \App\Events\SharedRideLocationUpdated(
                $ride->id,
                $agent->station->latitude,
                $agent->station->longitude,
                0
            ))->toOthers();
        } catch (\Exception $e) {}

        return response()->json([
            'success' => true,
            'message' => "Le véhicule est maintenant marqué à l'arrêt : " . $agent->station->name,
            'data' => $ride
        ]);
    }

    // =========================================================================
    // ARRÊT DE VILLE / CARREFOUR — Agent Booker Urbain
    // =========================================================================

    /**
     * Get available service types near the agent's stop + provider count & ETA.
     * For city-stop agents: shows what vehicles are nearby so agent can book for walk-in clients.
     */
    public function getAvailableServices(Request $request)
    {
        $agent = Auth::user()->stationAgent;
        if (!$agent) return response()->json(['error' => 'Unauthorized'], 403);

        $station = $agent->station;
        if (!$station) return response()->json(['error' => 'Arrêt non configuré.'], 400);

        $latitude = $station->latitude;
        $longitude = $station->longitude;
        $searchRadius = Setting::get('provider_search_radius', '10');

        // Get all active service types (or filter by allowed_service_types on the stop)
        $allowedTypes = $station->allowed_service_types; // JSON array or null
        $serviceTypes = ServiceType::where('status', 1);
        if (!empty($allowedTypes) && is_array($allowedTypes)) {
            $serviceTypes->whereIn('id', $allowedTypes);
        }
        $serviceTypes = $serviceTypes->get();

        $result = [];

        foreach ($serviceTypes as $st) {
            // Count nearby active providers for this service type
            $providers = Provider::with('service')
                ->select(DB::Raw("(6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) AS distance"), 'id')
                ->where('status', 'approved')
                ->whereRaw("(6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $searchRadius")
                ->whereHas('service', function ($query) use ($st) {
                    $query->where('status', 'active')
                          ->where('service_type_id', $st->id);
                })
                ->orderBy('distance', 'asc')
                ->take(20)
                ->get();

            $nearestDistance = $providers->first() ? $providers->first()->distance : null;
            // Estimation ETA: ~2 min/km en ville
            $etaMinutes = $nearestDistance ? round($nearestDistance * 2, 0) : null;

            $result[] = [
                'id' => $st->id,
                'name' => $st->name,
                'image' => $st->image_url,
                'capacity' => $st->capacity,
                'price_per_km' => $st->price,
                'fixed_price' => $st->fixed,
                'provider_count' => $providers->count(),
                'nearest_km' => $nearestDistance ? round($nearestDistance, 2) : null,
                'eta_minutes' => $etaMinutes,
                'available' => $providers->count() > 0,
            ];
        }

        return response()->json([
            'success' => true,
            'station' => [
                'id' => $station->id,
                'name' => $station->name,
                'type' => $station->type,
                'latitude' => $station->latitude,
                'longitude' => $station->longitude,
            ],
            'services' => $result
        ]);
    }

    /**
     * Agent books a ride on behalf of a walk-in client (Proxy Booking).
     * Replicates the user send_request flow but initiated by the agent.
     * The agent's stop coordinates are used as pickup location.
     */
    public function proxyBooking(Request $request)
    {
        $this->validate($request, [
            'service_type_id' => 'required|integer|exists:service_types,id',
            'customer_name'   => 'required|string',
            'customer_phone'  => 'required|string',
            'd_latitude'      => 'required|numeric',
            'd_longitude'     => 'required|numeric',
            'd_address'       => 'required|string',
            'seats'           => 'nullable|integer|min:1',
        ]);

        $agent = Auth::user()->stationAgent;
        if (!$agent) return response()->json(['error' => 'Unauthorized'], 403);

        $station = $agent->station;
        if (!$station) return response()->json(['error' => 'Arrêt non configuré.'], 400);

        $latitude  = $station->latitude;
        $longitude = $station->longitude;
        $service_type_id = $request->service_type_id;
        $searchRadius = Setting::get('provider_search_radius', '10');

        $serviceType = ServiceType::findOrFail($service_type_id);

        try {
            DB::beginTransaction();

            // 1. Find or create the customer user
            $customer = \App\Models\User::firstOrCreate(
                ['mobile' => $request->customer_phone],
                [
                    'first_name' => $request->customer_name,
                    'last_name'  => '',
                    'email'      => 'agent_proxy_' . time() . '@station.local',
                    'password'   => bcrypt(str_random(16)),
                    'user_type'  => 'USER'
                ]
            );

            // 2. Find nearby providers (same logic as UserApiController@send_request)
            $Providers = Provider::with('service')
                ->select(DB::Raw("(6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) AS distance"), 'id', 'eco_wallet_balance', 'service_type_id', 'commune')
                ->where('status', 'approved')
                ->whereRaw("(6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $searchRadius")
                ->whereHas('service', function ($query) use ($service_type_id) {
                    $query->where('status', 'active')
                          ->where('service_type_id', $service_type_id);
                })
                ->orderBy('distance', 'asc')
                ->take(10)
                ->get();

            if ($Providers->count() == 0) {
                DB::rollBack();
                return response()->json([
                    'error' => 'Aucun chauffeur disponible pour ce service à proximité.',
                    'provider_count' => 0
                ], 404);
            }

            // 3. Calculate distance and ETA
            $distance = $this->haversineDistance(
                $latitude, $longitude,
                $request->d_latitude, $request->d_longitude
            );

            $estimatedPrice = $serviceType->fixed + ($distance * $serviceType->price);
            $nearestDistance = $Providers->first()->distance;
            $etaMinutes = round($nearestDistance * 2, 0); // ~2min/km in city

            // 4. Create the UserRequest (ride)
            $bookingId = 'AGT-' . strtoupper(uniqid());
            $otp = strtoupper(substr($bookingId, -4));

            $userRequest = new UserRequests;
            $userRequest->booking_id = $bookingId;
            $userRequest->user_id = $customer->id;
            $userRequest->service_type_id = $service_type_id;
            $userRequest->payment_mode = 'CASH';
            $userRequest->status = 'SEARCHING';

            $userRequest->s_latitude  = $latitude;
            $userRequest->s_longitude = $longitude;
            $userRequest->s_address   = $station->name;
            $userRequest->d_latitude  = $request->d_latitude;
            $userRequest->d_longitude = $request->d_longitude;
            $userRequest->d_address   = $request->d_address;
            $userRequest->distance    = $distance;
            $userRequest->otp         = $otp;
            $userRequest->assigned_at = now();
            $userRequest->is_paid     = 0;
            $userRequest->seat_count  = $request->seats ?? 1;
            $userRequest->created_by_agent = $agent->id;

            // Broadcast mode: current_provider_id = 0
            if (Setting::get('broadcast_request', 0) == 1) {
                $userRequest->current_provider_id = 0;
            } else {
                $userRequest->current_provider_id = $Providers->first()->id;
            }

            $userRequest->save();

            // 5. Create RequestFilter entries and send push notifications (broadcast)
            foreach ($Providers as $Provider) {
                $Filter = new RequestFilter;
                $Filter->request_id  = $userRequest->id;
                $Filter->provider_id = $Provider->id;
                $Filter->save();

                // Send push notification
                try {
                    (new \App\Helpers\SendPushNotification)->IncomingRequest($Provider->id);
                } catch (\Exception $e) {
                    Log::warning("Push notification failed for provider {$Provider->id}: " . $e->getMessage());
                }
            }

            // 6. Modèle de Guichet : L'agent garde la commission, mais on débite cette somme de son solde virtuel
            $commissionPercentage = Setting::get('commission_percentage', 15); // Par défaut 15%
            $platformCommission = ($estimatedPrice * $commissionPercentage) / 100;
            
            // Plafond de découvert de -10000 FCFA
            $negativeLimit = -10000;
            if (($agent->wallet_balance - $platformCommission) < $negativeLimit) {
                DB::rollBack();
                return response()->json([
                    'error' => "Opération refusée : votre plafond de découvert (-10000 FCFA) serait dépassé. Veuillez recharger votre compte.",
                    'provider_count' => 0
                ], 400);
            }

            // Débit du portefeuille de l'agent
            $agent->decrement('wallet_balance', $platformCommission);

            DB::table('agent_commission_logs')->insert([
                'station_agent_id' => $agent->id,
                'type'             => 'PROXY_BOOKING_FEE',
                'amount'           => -$platformCommission,
                'reference_id'     => $userRequest->id,
                'description'      => "Commission retenue (Guichet): {$request->customer_name} → {$request->d_address}",
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            DB::commit();

            return response()->json([
                'success'         => true,
                'message'         => "Réservation envoyée ! Le chauffeur arrive dans ~{$etaMinutes} min.",
                'request_id'      => $userRequest->id,
                'booking_id'      => $bookingId,
                'otp'             => $otp,
                'eta_minutes'     => $etaMinutes,
                'estimated_price' => round($estimatedPrice),
                'platform_commission' => round($platformCommission),
                'amount_for_driver' => round($estimatedPrice - $platformCommission),
                'provider_count'  => $Providers->count(),
                'distance_km'     => round($distance, 1),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Proxy Booking Error: " . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la réservation proxy.'], 500);
        }
    }

    /**
     * Check the status of a proxy booking.
     * Allows agent to track if a driver has accepted the request.
     */
    public function proxyBookingStatus(Request $request)
    {
        $this->validate($request, [
            'request_id' => 'required|integer'
        ]);

        $agent = Auth::user()->stationAgent;
        if (!$agent) return response()->json(['error' => 'Unauthorized'], 403);

        $userRequest = UserRequests::where('id', $request->request_id)
            ->where('created_by_agent', $agent->id)
            ->with('provider')
            ->first();

        if (!$userRequest) {
            return response()->json(['error' => 'Réservation non trouvée.'], 404);
        }

        $providerInfo = null;
        if ($userRequest->provider_id && $userRequest->provider) {
            $provider = $userRequest->provider;
            $eta = $this->haversineDistance(
                $provider->latitude, $provider->longitude,
                $userRequest->s_latitude, $userRequest->s_longitude
            );

            $providerInfo = [
                'id'         => $provider->id,
                'name'       => $provider->first_name . ' ' . $provider->last_name,
                'mobile'     => $provider->mobile,
                'picture'    => $provider->picture,
                'latitude'   => $provider->latitude,
                'longitude'  => $provider->longitude,
                'eta_minutes'=> round($eta * 2, 0),
            ];
        }

        return response()->json([
            'success'  => true,
            'status'   => $userRequest->status,
            'booking_id' => $userRequest->booking_id,
            'otp'      => $userRequest->otp,
            'provider' => $providerInfo,
        ]);
    }

    /**
     * Calculate haversine distance between two GPS coordinates in km.
     */
    private function haversineDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }
}
