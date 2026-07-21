<?php

namespace App\Http\Controllers\ProviderResources;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\ActiveSharedRide;
use App\Models\PdpRoute;
use App\Models\PdpStop;
use App\Models\Provider;
use App\Models\ServiceType;
use Carbon\Carbon;

class SharedRideController extends Controller
{
    /**
     * Démarrer un service partagé sur un itinéraire approuvé
     * POST /api/provider/shared/rides/start
     */
    public function startRide(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'is_free_mode' => 'nullable|boolean',
            'pdp_route_id' => 'required_without:is_free_mode|exists:pdp_routes,id',
            'total_seats' => 'required|integer|min:1',
            'price_per_seat' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            $provider = Auth::guard('providerapi')->user();
            
            // Vérifier qu'il n'y a pas déjà un trajet actif pour ce provider
            $existingRide = ActiveSharedRide::where('provider_id', $provider->id)
                ->where('status', 'EN_ROUTE')
                ->first();

            if ($existingRide) {
                return response()->json([
                    'error' => 'Vous avez déjà un trajet actif. Terminez-le d\'abord.'
                ], 400);
            }

            $isFreeMode = $request->input('is_free_mode', false);
            
            $route = null;
            $firstStop = null;
            $direction = 'FORWARD';
            
            if (!$isFreeMode) {
                // Vérifier que l'itinéraire est approuvé
                $route = PdpRoute::findOrFail($request->pdp_route_id);
                if ($route->status !== 'APPROVED') {
                    return response()->json([
                        'error' => 'Cet itinéraire n\'est pas encore approuvé'
                    ], 400);
                }

                // Déterminer la direction (FORWARD par défaut)
                $direction = $request->input('direction', 'FORWARD');
                if (!in_array($direction, ['FORWARD', 'BACKWARD'])) {
                    $direction = 'FORWARD';
                }

                // Récupérer le premier arrêt de l'itinéraire selon la direction
                $firstStopQuery = PdpStop::where('pdp_route_id', $route->id);
                if ($direction === 'BACKWARD') {
                    $firstStop = $firstStopQuery->orderBy('order', 'desc')->first();
                } else {
                    $firstStop = $firstStopQuery->orderBy('order', 'asc')->first();
                }

                if (!$firstStop) {
                    return response()->json([
                        'error' => 'Aucun arrêt trouvé pour cet itinéraire'
                    ], 400);
                }
            }

            // Récupérer le service_type du provider
            $serviceTypeId = $provider->service_type_id ?? $request->service_type_id;
            if (!$serviceTypeId) {
                return response()->json([
                    'error' => 'Service type requis. Veuillez spécifier service_type_id ou l\'associer à votre profil provider.'
                ], 400);
            }

            // RESTRICTION : Voyage / Outstation DOIVENT utiliser une ligne fixe
            $serviceType = \App\Models\ServiceType::find($serviceTypeId);
            if ($serviceType) {
                $serviceName = strtolower($serviceType->name);
                $isVoyage = strpos($serviceName, 'voyage') !== false || 
                            strpos($serviceName, 'outstation') !== false || 
                            strpos($serviceName, 'bus') !== false || 
                            strpos($serviceName, 'mini') !== false; // car, bus, minibus
                
                if ($isVoyage && $isFreeMode) {
                    return response()->json([
                        'error' => 'Les véhicules de type ' . $serviceType->name . ' ne sont pas autorisés en Mode Libre. Vous devez obligatoirement sélectionner une Ligne Fixe.'
                    ], 403);
                }
            }

            // Prix par siège (depuis la route ou le request)
            $pricePerSeat = $request->input('price_per_seat');
            if (!$pricePerSeat && $route) {
                $pricePerSeat = $route->base_price_per_segment;
            }
            if (!$pricePerSeat) $pricePerSeat = 0;

            // Créer le trajet actif
            $activeRide = ActiveSharedRide::create([
                'is_free_mode' => $isFreeMode,
                'pdp_route_id' => $route ? $route->id : null,
                'provider_id' => $provider->id,
                'service_type_id' => $serviceTypeId,
                'vehicle_id' => $request->vehicle_id ?? null,
                'status' => 'EN_ROUTE',
                'total_seats' => $request->total_seats,
                'available_seats' => $request->total_seats,
                'price_per_seat' => $pricePerSeat,
                'current_latitude' => $provider->latitude ?? ($firstStop->latitude ?? 0),
                'current_longitude' => $provider->longitude ?? ($firstStop->longitude ?? 0),
                'next_stop_id' => $firstStop ? $firstStop->id : null,
                'current_stop_id' => $firstStop ? $firstStop->id : null,
                'direction' => $direction,
                'started_at' => Carbon::now(),
                'last_position_update' => Carbon::now(),
            ]);

            // PUBLIER SUR LE HUB SOCIAL (Communauté)
            try {
                $routeName = $route ? $route->name : "Trajet Libre";
                $postContent = "🚐 NOUVEAU TRAJET PARTAGÉ : {$routeName}\n";
                $postContent .= "📍 Départ imminent ! Places dispo : {$activeRide->available_seats}\n";
                $postContent .= "💰 Tarif : {$pricePerSeat} FCFA / place.";

                $post = \App\Models\Post::create([
                    'user_id' => null, // Post système lié au chauffeur
                    'type' => 'TRIP',
                    'trip_id' => $activeRide->id,
                    'trip_type' => 'shared',
                    'category' => 'TRANSPORT',
                    'pdp_route_id' => $route ? $route->id : null,
                    'service_type_id' => $serviceTypeId,
                    'content' => $postContent,
                    'is_shareable' => true,
                    'seats_available' => $activeRide->available_seats,
                    'price' => $pricePerSeat,
                    'status' => 'ACTIVE'
                ]);

                // Notification temps réel (Pusher)
                broadcast(new \App\Events\NewSocialTripPosted($post->id, 'TRIP', $post->pdp_route_id, [
                    'route_name' => $routeName,
                    'price' => $pricePerSeat
                ]))->toOthers();

                // NOTIFICATION PUSH (FCM)
                if ($route) {
                    (new \App\Http\Controllers\SendPushNotification)->CommunityTripCreated($route->id, "🚐 Nouveau trajet disponible sur votre corridor : {$routeName} !");
                }

            } catch (\Exception $e) {
                Log::warning("Erreur publication Social Hub lors du startRide: " . $e->getMessage());
            }

            return response()->json([
                'message' => 'Trajet démarré avec succès',
                'ride' => $activeRide->load(['route', 'nextStop', 'provider'])
            ], 201);

        } catch (\Exception $e) {
            Log::error('Erreur lors du démarrage du trajet: ' . $e->getMessage());
            return response()->json([
                'error' => 'Une erreur est survenue lors du démarrage du trajet'
            ], 500);
        }
    }

    /**
     * Mettre à jour la position GPS du véhicule
     * POST /api/provider/shared/rides/{id}/update-position
     */
    public function updatePosition(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            $provider = Auth::guard('providerapi')->user();
            
            $activeRide = ActiveSharedRide::where('id', $id)
                ->where('provider_id', $provider->id)
                ->where('status', 'EN_ROUTE')
                ->firstOrFail();

            $activeRide->update([
                'current_latitude' => $request->latitude,
                'current_longitude' => $request->longitude,
                'last_position_update' => Carbon::now(),
            ]);

            // Mettre à jour aussi la position du provider
            $provider->update([
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]);

            // NOUVEAU : Diffuser en temps réel pour le suivi passager (Map)
            broadcast(new \App\Events\SharedRideLocationUpdated($activeRide->id, $request->latitude, $request->longitude))->toOthers();

            return response()->json([
                'message' => 'Position mise à jour',
                'ride' => $activeRide->fresh()
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour de position: ' . $e->getMessage());
            return response()->json([
                'error' => 'Trajet introuvable ou terminé'
            ], 404);
        }
    }

    /**
     * Déclarer l'arrivée à un arrêt
     * POST /api/provider/shared/rides/{id}/arrive-at-stop
     */
    public function arriveAtStop(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'stop_id' => 'required|exists:pdp_stops,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            $provider = Auth::guard('providerapi')->user();
            
            $activeRide = ActiveSharedRide::where('id', $id)
                ->where('provider_id', $provider->id)
                ->where('status', 'EN_ROUTE')
                ->firstOrFail();

            $stop = PdpStop::findOrFail($request->stop_id);

            // Vérifier que l'arrêt appartient à l'itinéraire
            if ($stop->pdp_route_id !== $activeRide->pdp_route_id) {
                return response()->json([
                    'error' => 'Cet arrêt n\'appartient pas à cet itinéraire'
                ], 400);
            }

            // Mettre à jour l'arrêt actuel
            $activeRide->update([
                'current_stop_id' => $stop->id,
                'current_latitude' => $stop->latitude,
                'current_longitude' => $stop->longitude,
            ]);

            // Trouver le prochain arrêt selon la direction
            $nextStopQuery = PdpStop::where('pdp_route_id', $activeRide->pdp_route_id);
            
            if ($activeRide->direction === 'BACKWARD') {
                $nextStop = $nextStopQuery->where('order', '<', $stop->order)
                    ->orderBy('order', 'desc')
                    ->first();
            } else {
                $nextStop = $nextStopQuery->where('order', '>', $stop->order)
                    ->orderBy('order', 'asc')
                    ->first();
            }

            if ($nextStop) {
                $activeRide->update(['next_stop_id' => $nextStop->id]);
            } else {
                // BOUCLE WORO-WORO : Arrivé au terminus, on fait demi-tour automatiquement
                $newDirection = $activeRide->direction === 'FORWARD' ? 'BACKWARD' : 'FORWARD';
                
                // Trouver le premier arrêt du sens inverse (qui est l'arrêt précédent)
                $reverseNextStopQuery = PdpStop::where('pdp_route_id', $activeRide->pdp_route_id);
                if ($newDirection === 'BACKWARD') {
                    $nextStopReverse = $reverseNextStopQuery->where('order', '<', $stop->order)
                        ->orderBy('order', 'desc')
                        ->first();
                } else {
                    $nextStopReverse = $reverseNextStopQuery->where('order', '>', $stop->order)
                        ->orderBy('order', 'asc')
                        ->first();
                }

                if ($nextStopReverse) {
                    $activeRide->update([
                        'direction' => $newDirection,
                        'next_stop_id' => $nextStopReverse->id
                    ]);
                } else {
                    // C'est un cas anormal (ligne à 1 arrêt ?), on termine.
                    $activeRide->update([
                        'status' => 'TERMINATED',
                        'ended_at' => Carbon::now(),
                        'next_stop_id' => null,
                    ]);
                }
            }

            return response()->json([
                'message' => 'Arrivée à l\'arrêt enregistrée',
                'ride' => $activeRide->fresh(['currentStop', 'nextStop'])
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'arrivée à l\'arrêt: ' . $e->getMessage());
            return response()->json([
                'error' => 'Une erreur est survenue'
            ], 500);
        }
    }

    /**
     * Terminer un trajet
     * POST /api/provider/shared/rides/{id}/end
     */
    public function endRide(Request $request, $id)
    {
        try {
            $provider = Auth::guard('providerapi')->user();
            
            $activeRide = ActiveSharedRide::where('id', $id)
                ->where('provider_id', $provider->id)
                ->where('status', 'EN_ROUTE')
                ->firstOrFail();

            $activeRide->update([
                'status' => 'TERMINATED',
                'ended_at' => Carbon::now(),
            ]);

            return response()->json([
                'message' => 'Trajet terminé',
                'ride' => $activeRide->fresh()
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la fin du trajet: ' . $e->getMessage());
            return response()->json([
                'error' => 'Trajet introuvable ou déjà terminé'
            ], 404);
        }
    }

    /**
     * Obtenir les informations du trajet actif
     * GET /api/provider/shared/rides/current
     */
    public function getCurrentRide()
    {
        try {
            $provider = Auth::guard('providerapi')->user();
            
            $activeRide = ActiveSharedRide::where('provider_id', $provider->id)
                ->where('status', 'EN_ROUTE')
                ->with(['route', 'nextStop', 'currentStop', 'bookings.user', 'bookings.startStop', 'bookings.endStop'])
                ->first();

            if (!$activeRide) {
                return response()->json([
                    'message' => 'Aucun trajet actif',
                    'ride' => null
                ], 200);
            }

            return response()->json([
                'ride' => $activeRide
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération du trajet: ' . $e->getMessage());
            return response()->json([
                'error' => 'Une erreur est survenue'
            ], 500);
        }
    }
}

