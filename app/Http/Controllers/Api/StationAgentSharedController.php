<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StationAgent;
use App\Models\Partner;
use App\Models\WalletPassbook;
use App\Models\Provider;
use App\Models\PdpRoute;
use App\Models\User;
use App\Models\ActiveSharedRide;
use App\Models\RideBooking;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StationAgentSharedController extends Controller
{
    /**
     * Résoudre l'agent connecté : Partner STATION_AGENT (prioritaire) ou StationAgent legacy.
     */
    private function getAgent()
    {
        $user = Auth::user();
        if (!$user) return null;

        $partner = Partner::where('user_id', $user->id)
            ->where('type', 'STATION_AGENT')
            ->with('user')
            ->first();
        if ($partner) return $partner;

        return StationAgent::where('user_id', $user->id)->first();
    }

    /**
     * Retourne [Partner|null, StationAgent|null] pour les méthodes bi-système.
     */
    private function resolveActors(): array
    {
        $user    = Auth::user();
        $partner = $user ? Partner::where('user_id', $user->id)->where('type', 'STATION_AGENT')->with('user')->first() : null;
        $agent   = $user ? StationAgent::where('user_id', $user->id)->first() : null;
        return [$partner, $agent];
    }

    /**
     * Lancer le chargement d'un véhicule (Wôrô-wôrô) à la gare
     * POST /api/user/agent/shared/boarding/start
     */
    public function startBoarding(Request $request)
    {
        $agent = $this->getAgent();
        if (!$agent) {
            return response()->json(['error' => 'Non autorisé. Compte Agent introuvable.'], 403);
        }

        $request->validate([
            'provider_id' => 'required|exists:providers,id',
            'pdp_route_id' => 'required|exists:pdp_routes,id',
            'total_seats' => 'required|integer|min:1',
            'price_per_seat' => 'required|numeric|min:0'
        ]);

        $provider = Provider::findOrFail($request->provider_id);
        $route = PdpRoute::findOrFail($request->pdp_route_id);

        // Vérifier si le chauffeur n'a pas déjà un chargement en cours
        $existing = ActiveSharedRide::where('provider_id', $provider->id)
            ->where('status', 'EN_ROUTE')
            ->whereNull('started_at')
            ->first();

        if ($existing) {
            return response()->json([
                'error' => 'Ce chauffeur est déjà en cours de chargement sur un autre quai.',
                'active_shared_ride_id' => $existing->id
            ], 400);
        }

        // Création du trajet en chargement
        $ride = ActiveSharedRide::create([
            'pdp_route_id' => $route->id,
            'provider_id' => $provider->id,
            'service_type_id' => $provider->service ? $provider->service->service_type_id : null,
            'vehicle_id' => $provider->service ? $provider->service->id : null,
            'status' => 'EN_ROUTE',
            'total_seats' => $request->total_seats,
            'available_seats' => $request->total_seats,
            'price_per_seat' => $request->price_per_seat,
            'started_at' => null, // Indique "EN CHARGEMENT"
            'current_stop_id' => $route->start_stop_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ligne de chargement ouverte.',
            'ride' => $ride
        ]);
    }

    /**
     * Liste des véhicules en chargement à cette gare
     * GET /api/user/agent/shared/boarding
     */
    public function listBoardingRides(Request $request)
    {
        $agent = $this->getAgent();
        if (!$agent) {
            return response()->json(['error' => 'Non autorisé.'], 403);
        }

        // Récupérer les trajets où l'arrêt de départ correspond à la gare de l'agent
        // On suppose que l'agent est lié à un pdp_stop (sa gare) via station_id ou similaire.
        // Si non défini, on renvoie tout ce qui n'a pas démarré (simplification).
        
        $rides = ActiveSharedRide::with(['provider', 'route'])
            ->where('status', 'EN_ROUTE')
            ->whereNull('started_at')
            ->orderBy('created_at', 'desc')
            ->get();

        // Ajout des statistiques par trajet
        $rides->transform(function ($ride) {
            $ride->booked_seats = $ride->total_seats - $ride->available_seats;
            return $ride;
        });

        return response()->json([
            'success' => true,
            'data' => $rides
        ]);
    }

    /**
     * Ajouter un passager à un trajet en chargement
     * POST /api/user/agent/shared/boarding/add-passenger
     */
    public function addPassengerToRide(Request $request)
    {
        // Résolution bi-système
        [$partner, $agent] = $this->resolveActors();
        if (!$partner && !$agent) {
            return response()->json(['error' => 'Non autorisé.'], 403);
        }

        $ride = ActiveSharedRide::with('route')->findOrFail($request->active_shared_ride_id);

        if ($ride->started_at !== null) {
            return response()->json(['error' => 'Ce véhicule a déjà démarré.'], 400);
        }

        if (!$ride->hasAvailableSeats($request->seats)) {
            return response()->json(['error' => 'Pas assez de places disponibles dans ce véhicule.'], 400);
        }

        $totalPrice           = $ride->price_per_seat * $request->seats;
        $commissionPercentage = Setting::get('commission_percentage', 15);
        $platformCommission   = ($totalPrice * $commissionPercentage) / 100;
        $negativeLimit        = -10000;

        // Vérification du plafond de découvert selon le système actif
        if ($partner && $partner->user) {
            $currentBalance = $partner->user->wallet_balance ?? 0;
        } else {
            $currentBalance = $agent->wallet_balance ?? 0;
        }
        if (($currentBalance - $platformCommission) < $negativeLimit) {
            return response()->json([
                'error' => 'Opération refusée : plafond de découvert (-10000 FCFA) dépassé.',
            ], 400);
        }

        DB::beginTransaction();
        try {
            // 1. Créer ou trouver l'utilisateur client
            $customer = User::firstOrCreate(
                ['mobile' => $request->customer_phone],
                [
                    'first_name'   => $request->customer_name,
                    'last_name'    => '',
                    'email'        => 'shared_agent_' . time() . '@station.local',
                    'password'     => bcrypt(uniqid()),
                    'device_type'  => 'android',
                    'payment_mode' => 'CASH',
                ]
            );

            if ($customer->first_name !== $request->customer_name) {
                $customer->first_name = $request->customer_name;
                $customer->save();
            }

            // 2. Créer le booking
            $booking = RideBooking::create([
                'active_shared_ride_id' => $ride->id,
                'user_id'               => $customer->id,
                'start_stop_id'         => $ride->route->start_stop_id,
                'end_stop_id'           => $ride->route->end_stop_id,
                'seats_booked'          => $request->seats,
                'price'                 => $totalPrice,
                'status'                => 'COMPLETED',
            ]);

            // 3. Mettre à jour les places
            $ride->decrement('available_seats', $request->seats);

            // 4. Débiter la commission de l'agent (nouveau système ou legacy)
            if ($partner && $partner->user) {
                $partner->user->decrement('wallet_balance', $platformCommission);

                WalletPassbook::create([
                    'user_id'      => $partner->user->id,
                    'partner_id'   => $partner->id,
                    'amount'       => $platformCommission,
                    'status'       => 'DEBITED',
                    'via'          => 'SHARED_BOARDING_FEE',
                    'description'  => "Commission retenue (Wôrô-wôrô): {$customer->first_name} ({$request->seats} places)",
                    'reference_id' => (string) $booking->id,
                ]);
            } else {
                $agent->decrement('wallet_balance', $platformCommission);

                DB::table('agent_commission_logs')->insert([
                    'station_agent_id' => $agent->id,
                    'type'             => 'SHARED_BOARDING_FEE',
                    'amount'           => -$platformCommission,
                    'reference_id'     => $booking->id,
                    'description'      => "Commission retenue (Wôrô-wôrô): {$customer->first_name} ({$request->seats} places) [legacy]",
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success'             => true,
                'message'             => 'Passager embarqué avec succès.',
                'booking_id'          => $booking->id,
                'seats_left'          => $ride->available_seats,
                'total_price'         => round($totalPrice),
                'platform_commission' => round($platformCommission),
                'amount_for_driver'   => round($totalPrice - $platformCommission),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur Agent Boarding', ['err' => $e->getMessage()]);
            return response()->json(['error' => "Erreur lors de l'embarquement."], 500);
        }
    }

    /**
     * Donner le départ (Bon de sortie)
     * POST /api/user/agent/shared/boarding/{ride_id}/dispatch
     */
    public function dispatchRide($id)
    {
        $agent = $this->getAgent();
        if (!$agent) return response()->json(['error' => 'Non autorisé.'], 403);

        $ride = ActiveSharedRide::findOrFail($id);

        if ($ride->started_at !== null) {
            return response()->json(['error' => 'Le véhicule a déjà démarré.'], 400);
        }

        $ride->started_at = now();
        $ride->save();

        // Notifier le chauffeur (Push notification) si possible
        try {
            (new \App\Http\Controllers\SendPushNotification)->ProviderSimpleMessage(
                $ride->provider_id, 
                "Votre Wôrô-wôrô a été autorisé à partir ! Bon trajet."
            );
        } catch (\Exception $e) {
            // Ignorer si la notif échoue
        }

        return response()->json([
            'success' => true,
            'message' => 'Le bon de sortie a été émis. Véhicule en route !'
        ]);
    }
}
