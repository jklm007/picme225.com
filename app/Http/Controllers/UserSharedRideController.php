<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\ActiveSharedRide;
use App\Models\PdpRoute;
use App\Models\PdpRouteSegment;
use App\Models\PdpStop;
use App\Models\RideBooking;
use App\Models\ServiceType;
use App\Helpers\Helper;
use Carbon\Carbon;

class UserSharedRideController extends Controller
{
    /**
     * Rechercher les services partagés actifs à proximité
     * GET /api/user/shared/rides/nearby
     */
    public function nearby(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:0.1|max:50', // Rayon en km
            'pdp_route_id' => 'nullable|exists:pdp_routes,id',
            'start_stop_id' => 'nullable|exists:pdp_stops,id',
            'end_stop_id' => 'nullable|exists:pdp_stops,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            $userLat = $request->latitude;
            $userLng = $request->longitude;
            $radius = $request->radius ?? 5; // Par défaut 5 km

            $query = ActiveSharedRide::where('status', 'EN_ROUTE')
                ->where('available_seats', '>', 0)
                ->with(['route', 'nextStop', 'provider']);

            // Filtrer par itinéraire si spécifié
            if ($request->pdp_route_id) {
                $query->where(function ($q) use ($request) {
                    $q->where('pdp_route_id', $request->pdp_route_id)
                      ->orWhere('is_free_mode', 1);
                });
            }

            $activeRides = $query->get();

            // Filtrer par distance et par direction géospatiale Woro-Woro
            $startStop = null;
            $endStop = null;
            $passengerBearing = null;
            
            if ($request->start_stop_id && $request->end_stop_id) {
                $startStop = \App\Models\PdpStop::find($request->start_stop_id);
                $endStop = \App\Models\PdpStop::find($request->end_stop_id);
                if ($startStop && $endStop) {
                    $passengerBearing = Helper::getBearing($startStop->latitude, $startStop->longitude, $endStop->latitude, $endStop->longitude);
                }
            }

            $nearbyRides = $activeRides->filter(function ($ride) use ($userLat, $userLng, $radius, $passengerBearing, $endStop) {
                if (!$ride->current_latitude || !$ride->current_longitude) {
                    return false;
                }
                
                // WORO-WORO GÉOLOCALISATION : Si la destination est connue, vérifier si le chauffeur s'y dirige
                if ($passengerBearing !== null && $endStop) {
                    $driverToDestBearing = Helper::getBearing($ride->current_latitude, $ride->current_longitude, $endStop->latitude, $endStop->longitude);
                    $bearingDiff = abs($passengerBearing - $driverToDestBearing);
                    $bearingDiff = min($bearingDiff, 360 - $bearingDiff);
                    
                    // Si l'angle est supérieur à 60°, le chauffeur s'éloigne ou n'est plus aligné
                    if ($bearingDiff > 60) {
                        return false;
                    }
                }

                $distance = Helper::haversineGreatCircleDistance(
                    $userLat,
                    $userLng,
                    $ride->current_latitude,
                    $ride->current_longitude
                ) / 1000; // Convertir en km

                return $distance <= $radius;
            })->map(function ($ride) use ($userLat, $userLng) {
                $distance = Helper::haversineGreatCircleDistance(
                    $userLat,
                    $userLng,
                    $ride->current_latitude,
                    $ride->current_longitude
                ) / 1000;

                return [
                    'id' => $ride->id,
                    'is_free_mode' => (bool) $ride->is_free_mode,
                    'route' => $ride->is_free_mode ? [
                        'id' => null,
                        'name' => 'Woro-Woro (Mode Libre)',
                        'type' => 'ROAMING',
                    ] : [
                        'id' => $ride->route->id ?? null,
                        'name' => $ride->route->name ?? 'Inconnu',
                        'type' => $ride->route->type ?? 'Inconnu',
                    ],
                    'provider' => [
                        'id' => $ride->provider->id,
                        'name' => $ride->provider->first_name . ' ' . $ride->provider->last_name,
                        'rating' => $ride->provider->rating,
                    ],
                    'available_seats' => $ride->available_seats,
                    'total_seats' => $ride->total_seats,
                    'current_position' => [
                        'latitude' => $ride->current_latitude,
                        'longitude' => $ride->current_longitude,
                    ],
                    'next_stop' => $ride->nextStop ? [
                        'id' => $ride->nextStop->id,
                        'name' => $ride->nextStop->name,
                        'latitude' => $ride->nextStop->latitude,
                        'longitude' => $ride->nextStop->longitude,
                    ] : null,
                    'distance_km' => round($distance, 2),
                    'direction' => $ride->direction ?? 'FORWARD',
                    'started_at' => $ride->started_at,
                ];
            })->sortBy('distance_km')->values();

            return response()->json([
                'rides' => $nearbyRides,
                'count' => $nearbyRides->count()
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la recherche de trajets: ' . $e->getMessage());
            return response()->json([
                'error' => 'Une erreur est survenue lors de la recherche'
            ], 500);
        }
    }

    /**
     * Réserver une place dans un trajet partagé
     * 
     * Deux types de trajets supportés:
     * 1. Arrêt à arrêt (standard): Pas de detour_latitude/longitude
     *    - Prix = Somme des prix des segments traversés
     * 
     * 2. Porte-à-porte (avec détour): detour_latitude/longitude fournis
     *    - Validation: distance_aller ≤ max_detour
     *    - Facturation: (distance_aller + distance_retour) × price_per_km
     *    - Prix = Prix segments + Prix détour
     * 
     * POST /api/user/shared/rides/{rideId}/book
     */
    public function book(Request $request, $rideId)
    {
        $validator = Validator::make($request->all(), [
            'start_stop_id' => 'required|exists:pdp_stops,id',
            'end_stop_id' => 'required|exists:pdp_stops,id|different:start_stop_id',
            'seats_booked' => 'required|integer|min:1|max:10',
            'detour_latitude' => 'nullable|numeric|between:-90,90',
            'detour_longitude' => 'nullable|numeric|between:-180,180',
            'payment_mode' => 'required|in:CASH,CARD,PAYPAL,WALLET',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            $user = Auth::guard('api')->user();
            
            $activeRide = ActiveSharedRide::where('id', $rideId)
                ->where('status', 'EN_ROUTE')
                ->with(['route', 'serviceType'])
                ->firstOrFail();

            // Vérifier les places disponibles
            if ($activeRide->available_seats < $request->seats_booked) {
                return response()->json([
                    'error' => 'Pas assez de places disponibles'
                ], 400);
            }

            $startStop = PdpStop::findOrFail($request->start_stop_id);
            $endStop = PdpStop::findOrFail($request->end_stop_id);

            // --- Validation Communale / Intercommunale ---
            if ($activeRide->serviceType && $activeRide->serviceType->is_communal) {
                // 1. Vérifier si le service est rattaché à une commune spécifique
                if ($activeRide->serviceType->commune && $startStop->commune && $startStop->commune !== $activeRide->serviceType->commune) {
                    return response()->json([
                        'error' => "Ce service est un service communal et est restreint à votre commune. Vous ne pouvez pas utiliser ce service pour sortir de votre commune."
                    ], 400);
                }

                // 2. Vérifier que le trajet ne sort pas de la commune
                if ($startStop->commune && $endStop->commune && $startStop->commune !== $endStop->commune) {
                    return response()->json([
                        'error' => "Ce service est un service communal et est restreint à votre commune. Vous ne pouvez pas utiliser ce service pour sortir de votre commune."
                    ], 400);
                }
            }

            // Vérifier que les arrêts appartiennent à l'itinéraire (si applicable)
            if (!$activeRide->is_free_mode) {
                if ($startStop->pdp_route_id !== $activeRide->pdp_route_id || 
                    $endStop->pdp_route_id !== $activeRide->pdp_route_id) {
                    return response()->json([
                        'error' => 'Les arrêts doivent appartenir à l\'itinéraire du trajet'
                    ], 400);
                }
            }

            // Vérifier le sens du trajet en temps réel (Géolocalisation Woro-Woro Libre)
            $passengerBearing = Helper::getBearing($startStop->latitude, $startStop->longitude, $endStop->latitude, $endStop->longitude);
            $driverToDestBearing = Helper::getBearing($activeRide->current_latitude, $activeRide->current_longitude, $endStop->latitude, $endStop->longitude);
            
            $bearingDiff = abs($passengerBearing - $driverToDestBearing);
            $bearingDiff = min($bearingDiff, 360 - $bearingDiff);
            
            if ($bearingDiff > 60) {
                return response()->json([
                    'error' => 'Ce véhicule s\'éloigne géographiquement de votre destination'
                ], 400);
            }

            // Calculer le prix
            $price = $this->calculateRidePrice($activeRide->route, $activeRide->serviceType, $startStop, $endStop, $request, $request->seats_booked);

            // Créer la réservation
            $booking = RideBooking::create([
                'active_shared_ride_id' => $activeRide->id,
                'user_id' => $user->id,
                'start_stop_id' => $startStop->id,
                'end_stop_id' => $endStop->id,
                'seats_booked' => $request->seats_booked,
                'price' => $price['final_base_price'],
                'detour_distance' => $price['detour_distance_total'],
                'detour_price' => $price['final_detour_price'],
                'status' => 'CONFIRMED',
                'payment_mode' => $request->payment_mode,
            ]);

            // Décrémenter les places disponibles
            $activeRide->decrement('available_seats', $request->seats_booked);

            return response()->json([
                'message' => 'Réservation confirmée',
                'booking' => $booking->load(['startStop', 'endStop', 'activeRide.route', 'activeRide.serviceType']),
                'price' => [
                    'base_price' => $price['base_price'],
                    'final_base_price' => $price['final_base_price'],
                    'segments_count' => $price['segments_count'],
                    'segment_details' => $price['segment_details'],
                    'total_segment_distance_km' => $price['total_segment_distance_km'],
                    'detour_distance_aller_km' => $price['detour_distance_aller'],
                    'detour_distance_retour_km' => $price['detour_distance_retour'],
                    'detour_distance_total_km' => $price['detour_distance_total'],
                    'detour_price' => $price['detour_price'],
                    'final_detour_price' => $price['final_detour_price'],
                    'detour_valid' => $price['detour_valid'],
                    'free_km_per_passenger' => $price['free_km_per_passenger'],
                    'total_free_km' => $price['total_free_km'],
                    'total_distance_km' => $price['total_distance_km'],
                    'payable_distance_km' => $price['payable_distance_km'],
                    'price_reduction' => $price['price_reduction'],
                    'total_price' => $price['total_price'],
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la réservation: ' . $e->getMessage());
            return response()->json([
                'error' => 'Une erreur est survenue lors de la réservation'
            ], 500);
        }
    }

    /**
     * Calculer le prix d'un trajet
     * POST /api/user/shared/rides/calculate-price
     */
    public function calculatePrice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pdp_route_id' => 'nullable|exists:pdp_routes,id',
            'start_stop_id' => 'required|exists:pdp_stops,id',
            'end_stop_id' => 'required|exists:pdp_stops,id|different:start_stop_id',
            'service_type_id' => 'nullable|exists:service_types,id',
            'seats_booked' => 'nullable|integer|min:1|max:10',
            'detour_latitude' => 'nullable|numeric|between:-90,90',
            'detour_longitude' => 'nullable|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            $route = null;
            if ($request->pdp_route_id) {
                $route = PdpRoute::findOrFail($request->pdp_route_id);
            }
            $startStop = PdpStop::findOrFail($request->start_stop_id);
            $endStop = PdpStop::findOrFail($request->end_stop_id);
            
            // Récupérer le service_type depuis la requête ou utiliser un par défaut
            $serviceType = null;
            if ($request->service_type_id) {
                $serviceType = ServiceType::findOrFail($request->service_type_id);
            }

            $seatsBooked = $request->seats_booked ?? 1;

            // --- Validation Communale pour l'estimation ---
            if ($serviceType && $serviceType->is_communal) {
                if ($serviceType->commune && $startStop->commune && $startStop->commune !== $serviceType->commune) {
                    return response()->json([
                        'status' => false,
                        'error' => "Ce service est un service communal et est restreint à votre commune. Vous ne pouvez pas utiliser ce service pour sortir de votre commune."
                    ], 400);
                }

                if ($startStop->commune && $endStop->commune && $startStop->commune !== $endStop->commune) {
                    return response()->json([
                        'status' => false,
                        'error' => "Ce service est un service communal et est restreint à votre commune. Vous ne pouvez pas utiliser ce service pour sortir de votre commune."
                    ], 400);
                }
            }

            $price = $this->calculateRidePrice($route, $serviceType, $startStop, $endStop, $request, $seatsBooked);

            return response()->json([
                'status' => true,
                'price' => [
                    'total_price' => (float)($price['total_price'] ?? 0),
                    'base_price' => (float)($price['base_price'] ?? 0),
                    'final_base_price' => (float)($price['final_base_price'] ?? 0),
                    'segments_count' => $price['segments_count'],
                    'segment_details' => $price['segment_details'],
                    'total_segment_distance_km' => round((float)($price['total_segment_distance_km'] ?? 0), 2),
                    'detour_distance_total_km' => round((float)($price['detour_distance_total'] ?? 0), 2),
                    'final_detour_price' => (float)($price['final_detour_price'] ?? 0),
                    'total_distance_km' => round((float)($price['total_distance_km'] ?? 0), 2),
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erreur lors du calcul du prix: ' . $e->getMessage());
            return response()->json([
                'error' => 'Une erreur est survenue lors du calcul du prix'
            ], 500);
        }
    }

    /**
     * Méthode privée pour calculer le prix
     * 
     * Logique:
     * - Chaque segment a un prix fixe (varie selon commune et distance)
     * - Prix total = somme des prix fixes des segments traversés
     * - Prix de base par segment: 200 FCFA (peut varier)
     * - Pour porte-à-porte: validation avec max_detour et max_waiting_time (pas de prix supplémentaire)
     */
    private function calculateRidePrice($route, $serviceType, $startStop, $endStop, $request, $seatsBooked = 1)
    {
        // Déterminer l'ordre de départ et d'arrivée
        $startOrder = $startStop->order;
        $endOrder = $endStop->order;
        
        // Déterminer le sens du trajet
        $isForward = $endOrder > $startOrder;
        $minOrder = min($startOrder, $endOrder);
        $maxOrder = max($startOrder, $endOrder);
        
        // Récupérer les segments entre les deux arrêts
        $segments = collect();
        if ($route) {
            $segments = PdpRouteSegment::where('pdp_route_id', $route->id)
                ->where('is_active', true)
                ->where('order', '>=', $minOrder)
                ->where('order', '<', $maxOrder)
                ->with(['fromStop', 'toStop'])
                ->orderBy('order')
                ->get();
        }

        // Calculer la distance totale des segments pour appliquer les km gratuits
        $totalSegmentDistance = 0;
        
        // Si aucun segment défini, utiliser le prix par défaut (200 FCFA) × nombre de segments
        $basePrice = 0;
        $segmentDetails = [];
        
        if ($segments->isEmpty()) {
            // Mode Libre (Woro-Woro) ou Photon : Utilisation de segments virtuels (2.5 km = 1 segment)
            $totalSegmentDistance = Helper::haversineGreatCircleDistance(
                $startStop->latitude,
                $startStop->longitude,
                $endStop->latitude,
                $endStop->longitude
            ) / 1000; // Convertir en km
            
            // Prix par segment défini par l'admin (fallback à 200 FCFA)
            $pricePerSegment = $serviceType ? (float)($serviceType->price_per_segment ?? 200) : 200;
            
            // Distance d'un segment définie par l'admin ou par défaut 2.5 km
            $kmPerSegment = ($serviceType && $serviceType->km_per_segment > 0) ? (float)$serviceType->km_per_segment : 2.5;
            
            // Nombre de segments (toujours arrondi au supérieur)
            $numSegments = ceil($totalSegmentDistance / $kmPerSegment);
            if ($numSegments < 1) $numSegments = 1;
            
            $basePrice = $numSegments * $pricePerSegment;
            
            $segmentDetails[] = [
                'order' => 1,
                'price' => round($basePrice, 2),
                'distance_km' => round($totalSegmentDistance, 2),
                'from_stop' => $startStop->name,
                'to_stop' => $endStop->name,
                'virtual_segments' => $numSegments
            ];
        } else {
            // Additionner les prix fixes de chaque segment et calculer la distance totale
            foreach ($segments as $segment) {
                // Utiliser le prix du segment de la DB, ou fallback sur price_per_segment du service
                $price = (float) ($segment->price ?? ($serviceType->price_per_segment ?? 200));
                $basePrice += $price;
                $totalSegmentDistance += (float) ($segment->distance_km ?? 0);
                $segmentDetails[] = [
                    'order' => $segment->order,
                    'price' => $price,
                    'distance_km' => (float) ($segment->distance_km ?? 0),
                    'from_stop' => $segment->fromStop->name ?? 'N/A',
                    'to_stop' => $segment->toStop->name ?? 'N/A',
                ];
            }
        }
        
        // Sécurité prix minimum
        if ($basePrice <= 0 && $startStop->id != $endStop->id) {
            $basePrice = $serviceType ? (float)($serviceType->price_per_segment ?? 200) : 200;
        }
        
        // Calculer les km gratuits (free_km_per_passenger × nombre de passagers)
        $freeKmPerPassenger = $serviceType ? (int)($serviceType->free_km_per_passenger ?? 0) : 0;
        $totalFreeKm = $freeKmPerPassenger * $seatsBooked;
        
        // Calculer la distance totale du trajet (segments + détour si applicable)
        // On calculera la distance totale après avoir calculé le détour

        // Validation et facturation du détour pour porte-à-porte
        $detourDistanceAller = 0;
        $detourDistanceRetour = 0;
        $detourDistanceTotal = 0;
        $detourPrice = 0;
        $detourValid = true;
        $detourValidationMessage = null;

        if ($request->detour_latitude && $request->detour_longitude) {
            // Calculer la distance ALLER (arrêt de départ → position client)
            $detourDistanceAller = Helper::haversineGreatCircleDistance(
                $startStop->latitude,
                $startStop->longitude,
                $request->detour_latitude,
                $request->detour_longitude
            ) / 1000; // Convertir en km

            // VALIDATION : Vérifier uniquement la distance ALLER
            $maxDetour = 5.0; // 5 km par défaut
            if ($serviceType) {
                if (isset($serviceType->is_communal) && $serviceType->is_communal) {
                    $maxDetour = $serviceType->max_detour_communal ?? 5.0;
                } else {
                    $maxDetour = $serviceType->max_detour_intercommunal ?? ($serviceType->max_detour ?? 5.0);
                }
            }

            if ($detourDistanceAller > $maxDetour) {
                $detourValid = false;
                $detourValidationMessage = "La distance aller pour récupérer le client ({$detourDistanceAller} km) dépasse le maximum autorisé ({$maxDetour} km)";
            }

            // Si validation OK, calculer la distance RETOUR (position client → arrêt de départ)
            if ($detourValid) {
                $detourDistanceRetour = Helper::haversineGreatCircleDistance(
                    $request->detour_latitude,
                    $request->detour_longitude,
                    $startStop->latitude,
                    $startStop->longitude
                ) / 1000; // Convertir en km

                // Distance totale facturable = aller + retour
                $detourDistanceTotal = $detourDistanceAller + $detourDistanceRetour;

                // FACTURATION : Prix du détour = distance totale × prix au km
                $pricePerKm = $serviceType ? (float)($serviceType->price_per_km ?? 200) : 200; // Défaut: 200 FCFA/km
                $detourPrice = $detourDistanceTotal * $pricePerKm;
            }

            // Vérifier aussi max_waiting_time de l'arrêt si disponible
            if ($detourValid && $startStop->max_waiting_time) {
                // Ici on pourrait ajouter une validation basée sur le temps d'attente
                // Pour l'instant, on valide juste la distance
            }
        }

        if (!$detourValid) {
            throw new \Exception($detourValidationMessage);
        }

        // Calculer la distance totale du trajet (segments + détour)
        $totalDistance = $totalSegmentDistance + $detourDistanceTotal;
        
        // --- Validation Géo-Zone (Rayon d'action max) ---
        if ($serviceType && $serviceType->max_distance > 0) {
            if ($totalDistance > $serviceType->max_distance) {
                throw new \Exception("Ce trajet ({$totalDistance} km) dépasse le rayon d'action maximum autorisé pour ce service ({$serviceType->max_distance} km).");
            }
        }
        
        // Appliquer les km gratuits sur la distance totale
        $payableDistance = max(0, $totalDistance - $totalFreeKm);
        
        // Calculer la réduction de prix basée sur les km gratuits
        // Si la distance totale est <= km gratuits, le trajet est gratuit
        // Sinon, on réduit proportionnellement
        $priceReduction = 0;
        $finalBasePrice = $basePrice;
        $finalDetourPrice = $detourPrice;
        
        if ($totalFreeKm > 0 && $totalDistance > 0) {
            if ($totalDistance <= $totalFreeKm) {
                // Trajet entièrement couvert par les km gratuits
                $priceReduction = $basePrice + $detourPrice;
                $finalBasePrice = 0;
                $finalDetourPrice = 0;
            } else {
                // Réduction proportionnelle basée sur le ratio km gratuits / distance totale
                $reductionRatio = $totalFreeKm / $totalDistance;
                
                // Répartir la réduction proportionnellement entre segments et détour
                $totalPriceBeforeReduction = $basePrice + $detourPrice;
                if ($totalPriceBeforeReduction > 0) {
                    $basePriceRatio = $basePrice / $totalPriceBeforeReduction;
                    $detourPriceRatio = $detourPrice / $totalPriceBeforeReduction;
                    
                    $priceReduction = $totalPriceBeforeReduction * $reductionRatio;
                    $finalBasePrice = max(0, $basePrice - ($priceReduction * $basePriceRatio));
                    $finalDetourPrice = max(0, $detourPrice - ($priceReduction * $detourPriceRatio));
                }
            }
        }
        
        $totalPrice = $finalBasePrice + $finalDetourPrice;

        return [
            'base_price' => $basePrice,
            'final_base_price' => $finalBasePrice,
            'segments_count' => count($segmentDetails),
            'segment_details' => $segmentDetails,
            'total_segment_distance_km' => round($totalSegmentDistance, 2),
            'detour_distance_aller' => round($detourDistanceAller, 2),
            'detour_distance_retour' => round($detourDistanceRetour, 2),
            'detour_distance_total' => round($detourDistanceTotal, 2),
            'detour_price' => $detourPrice,
            'final_detour_price' => $finalDetourPrice,
            'detour_valid' => $detourValid,
            'free_km_per_passenger' => $freeKmPerPassenger,
            'total_free_km' => $totalFreeKm,
            'total_distance_km' => round($totalDistance, 2),
            'payable_distance_km' => round($payableDistance, 2),
            'price_reduction' => round($priceReduction, 2),
            'total_price' => round($totalPrice, 2),
        ];
    }

    /**
     * Obtenir les réservations de l'utilisateur
     * GET /api/user/shared/rides/bookings
     */
    public function myBookings()
    {
        try {
            $user = Auth::guard('api')->user();
            
            $bookings = RideBooking::where('user_id', $user->id)
                ->with(['activeRide.route', 'startStop', 'endStop'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'bookings' => $bookings
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des réservations: ' . $e->getMessage());
            return response()->json([
                'error' => 'Une erreur est survenue'
            ], 500);
        }
    }

    /**
     * Annuler une réservation
     * POST /api/user/shared/rides/bookings/{id}/cancel
     */
    public function cancelBooking($id)
    {
        try {
            $user = Auth::guard('api')->user();
            
            $booking = RideBooking::where('id', $id)
                ->where('user_id', $user->id)
                ->where('status', 'CONFIRMED')
                ->firstOrFail();

            // Remettre les places disponibles
            $booking->activeRide->increment('available_seats', $booking->seats_booked);

            $booking->update([
                'status' => 'CANCELLED',
                'cancellation_reason' => 'Annulé par l\'utilisateur',
            ]);

            return response()->json([
                'message' => 'Réservation annulée',
                'booking' => $booking->fresh()
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'annulation: ' . $e->getMessage());
            return response()->json([
                'error' => 'Réservation introuvable ou déjà annulée'
            ], 404);
        }
    }
}

