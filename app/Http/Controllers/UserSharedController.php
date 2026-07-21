<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\PdpStop;
use App\Models\Provider;
use App\Models\ProviderService;
use App\Services\SharedTripService;
use App\Models\ServiceType;
use App\Models\UserRequestPassenger;
use App\Models\UserRequests;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Setting;

class UserSharedController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'service_type_id' => 'required|exists:service_types,id',
            'payment_mode' => ['required', Rule::in(['CASH', 'CARD', 'PAYPAL'])],
            's_latitude' => 'required|numeric',
            's_longitude' => 'required|numeric',
            's_address' => 'required|string|max:255',
            'd_latitude' => 'required|numeric',
            'd_longitude' => 'required|numeric',
            'd_address' => 'required|string|max:255',
            'grouping_point_id' => 'nullable|exists:pdp_stops,id',
            'segments' => 'required',
            'segments.*.s_latitude' => 'required|numeric',
            'segments.*.s_longitude' => 'required|numeric',
            'segments.*.d_latitude' => 'required|numeric',
            'segments.*.d_longitude' => 'required|numeric',
            'segments.*.price' => 'nullable|numeric|min:0',
            'segments.*.segment_name' => 'nullable|string|max:255',
            'segments.*.user_id' => 'nullable|exists:users,id',
            'passenger_ids' => 'nullable|array',
            'passenger_ids.*' => 'distinct|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $serviceType = ServiceType::findOrFail($request->service_type_id);
        $segments = SharedTripService::normalizeSegments($request->segments);

        if (empty($segments)) {
            return response()->json(['error' => trans('api.ride.invalid_segments')], 422);
        }

        $enrichedSegments = SharedTripService::hydrateSegmentsWithEstimates($segments);
        $segmentCollection = collect($enrichedSegments);
        $passengerIds = collect($request->passenger_ids ?? [])->unique()->filter();
        $seatsRequested = 1 + $passengerIds->count();

        if ($seatsRequested > $serviceType->capacity) {
            return response()->json(['error' => trans('api.ride.capacity_exceeded')], 422);
        }

        try {
            $userRequest = new UserRequests();
            $userRequest->booking_id = Helper::generate_booking_id();
            $userRequest->user_id = Auth::id();
            $userRequest->provider_id = 0;
            $userRequest->current_provider_id = 0;
            $userRequest->service_type_id = $serviceType->id;
            $userRequest->total_capacity = $serviceType->capacity;
            $userRequest->seats_booked = $seatsRequested;
            $userRequest->payment_mode = $request->payment_mode;
            $userRequest->status = 'MATCHING';
            $userRequest->segments = $enrichedSegments;
            $userRequest->grouping_point_id = $request->grouping_point_id;
            $userRequest->s_address = $request->s_address;
            $userRequest->s_latitude = $request->s_latitude;
            $userRequest->s_longitude = $request->s_longitude;
            $userRequest->d_address = $request->d_address;
            $userRequest->d_latitude = $request->d_latitude;
            $userRequest->d_longitude = $request->d_longitude;
            $userRequest->distance = round(SharedTripService::totalDistanceKm($segments), 2);
            $userRequest->otp = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            $userRequest->assigned_at = Carbon::now();
            $userRequest->method = 'share';
            $userRequest->route_key = '';
            $userRequest->package_id = 0;
            $userRequest->use_wallet = 0;
            $userRequest->surge = 0;
            $userRequest->save();

            $passengerIds->each(function ($passengerId) use ($userRequest) {
                UserRequestPassenger::firstOrCreate(
                    [
                        'request_id' => $userRequest->id,
                        'user_id' => $passengerId,
                    ],
                    [
                        'status' => 'PENDING',
                        'segment_type' => 'passenger',
                        'passengers_count' => 1,
                        'baggage_count' => 0,
                    ]
                );
            });

            $fare = SharedTripService::estimateFare($serviceType, $segments, $seatsRequested);

            return response()->json([
                'message' => trans('api.ride.created'),
                'request_id' => $userRequest->id,
                'fare' => $fare,
                'segments' => $segmentCollection,
            ], 201);
        } catch (Exception $e) {
            Log::error('Error while creating shared trip', ['exception' => $e]);
            return response()->json(['error' => trans('api.something_went_wrong')], 500);
        }
    }

    public function routeDetails($requestId): JsonResponse
    {
        $request = UserRequests::with('groupingPoint')
            ->findOrFail($requestId);

        $serviceType = ServiceType::findOrFail($request->service_type_id);
        $segments = SharedTripService::hydrateSegmentsWithEstimates($request->segments ?? []);
        $fare = SharedTripService::estimateFare($serviceType, $request->segments ?? []);

        return response()->json([
            'request' => $request,
            'segments' => $segments,
            'fare' => $fare,
        ]);
    }

    public function getDrivers($requestId): JsonResponse
    {
        $userRequest = UserRequests::findOrFail($requestId);
        $serviceType = ServiceType::findOrFail($userRequest->service_type_id);

        $maxDetour = SharedTripService::maxDetour($serviceType);
        $maxStopDistanceKm = SharedTripService::maxStopDistanceKm();
        $segments = $userRequest->segments ?? [];

        $activeProviders = ProviderService::AvailableServiceProvider($serviceType->id)
            ->pluck('provider_id');

        $distance = Setting::get('provider_search_radius', 10);
        $latitude = $userRequest->s_latitude;
        $longitude = $userRequest->s_longitude;

        $providers = Provider::select('providers.*')
            ->selectRaw("(6371 * acos( cos( radians(?) ) * cos( radians(latitude) ) * cos( radians(longitude) - radians(?) ) + sin( radians(?) ) * sin( radians(latitude) ) ) ) AS distance", [$latitude, $longitude, $latitude])
            ->whereIn('providers.id', $activeProviders)
            ->where('providers.status', 'approved')
            ->having('distance', '<=', $distance)
            ->orderBy('distance')
            ->get();

        $drivers = collect();

        foreach ($providers as $provider) {
            $distanceFromPickup = Helper::haversineGreatCircleDistance(
                $provider->latitude,
                $provider->longitude,
                $latitude,
                $longitude
            ) / 1000;

            $providerBearing = $this->providerBearing($provider, $userRequest);
            $requestBearing = Helper::getBearing(
                $userRequest->s_latitude,
                $userRequest->s_longitude,
                $userRequest->d_latitude,
                $userRequest->d_longitude
            );

            $bearingDiff = abs($requestBearing - $providerBearing);
            $bearingDiff = min($bearingDiff, 360 - $bearingDiff);
            $tolerance = max(30, 60 - $distanceFromPickup);

            if ($bearingDiff > $tolerance) {
                continue;
            }

            $availableSeats = $this->availableSeatsForProvider($provider->id, $serviceType->capacity);
            if ($availableSeats < $userRequest->seats_booked) {
                continue;
            }

            $detourMinutes = $this->calculateDetourMinutes(
                $provider,
                $segments,
                $maxStopDistanceKm
            );

            if ($detourMinutes === null || $detourMinutes > $maxDetour) {
                continue;
            }

            $drivers->push([
                'id' => $provider->id,
                'first_name' => $provider->first_name,
                'last_name' => $provider->last_name,
                'rating' => $provider->rating,
                'avatar' => $provider->avatar,
                'latitude' => $provider->latitude,
                'longitude' => $provider->longitude,
                'distance' => round($provider->distance, 2),
                'bearing_diff' => round($bearingDiff, 2),
                'detour_minutes' => round($detourMinutes, 2),
                'available_seats' => $availableSeats,
            ]);
        }

        $sortedDrivers = $drivers->sort(function ($a, $b) {
            return [$a['bearing_diff'], $a['distance'], $a['detour_minutes']]
                <=> [$b['bearing_diff'], $b['distance'], $b['detour_minutes']];
        })->values();

        return response()->json([
            'drivers' => $sortedDrivers,
        ]);
    }

    public function addPassenger(Request $request, $requestId): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'baggage_count' => 'nullable|integer|min:0',
        ]);

        $userRequest = UserRequests::findOrFail($requestId);
        $serviceType = ServiceType::findOrFail($userRequest->service_type_id);

        if (!in_array($userRequest->status, ['MATCHING', 'SEARCHING_MULTI', 'ACCEPTED_MULTI', 'IN_PROGRESS_MULTI'])) {
            return response()->json(['error' => trans('api.ride.cannot_add_passenger')], 422);
        }

        if ($userRequest->passengers()->where('user_id', $request->user_id)->exists()) {
            return response()->json(['error' => trans('api.ride.passenger_exists')], 409);
        }

        if ($userRequest->seats_booked + 1 > $serviceType->capacity) {
            return response()->json(['error' => trans('api.ride.capacity_exceeded')], 422);
        }

        UserRequestPassenger::create([
            'request_id' => $userRequest->id,
            'user_id' => $request->user_id,
            'segment_type' => 'passenger',
            'status' => 'PENDING',
            'passengers_count' => 1,
            'baggage_count' => (int) $request->baggage_count,
        ]);

        $userRequest->increment('seats_booked');

        return response()->json([
            'message' => trans('api.ride.passenger_added'),
            'seats_booked' => $userRequest->seats_booked,
        ]);
    }

    public function removePassenger($requestId, $passengerId): JsonResponse
    {
        $userRequest = UserRequests::findOrFail($requestId);

        $passenger = $userRequest->passengers()
            ->where('user_id', $passengerId)
            ->first();

        if (!$passenger) {
            return response()->json(['error' => trans('api.ride.passenger_not_found')], 404);
        }

        $passenger->delete();
        $userRequest->seats_booked = max(1, $userRequest->seats_booked - 1);
        $userRequest->save();

        return response()->json([
            'message' => trans('api.ride.passenger_removed'),
            'seats_booked' => $userRequest->seats_booked,
        ]);
    }

    private function providerBearing(Provider $provider, UserRequests $request): float
    {
        $activeRequest = UserRequests::where('provider_id', $provider->id)
            ->whereIn('status', ['IN_PROGRESS_MULTI', 'ACCEPTED_MULTI', 'STARTED'])
            ->latest('id')
            ->first();

        if ($activeRequest) {
            return Helper::getBearing(
                $provider->latitude,
                $provider->longitude,
                $activeRequest->d_latitude,
                $activeRequest->d_longitude
            );
        }

        return Helper::getBearing(
            $provider->latitude,
            $provider->longitude,
            $request->d_latitude,
            $request->d_longitude
        );
    }

    private function availableSeatsForProvider(int $providerId, int $capacity): int
    {
        $activeSeats = UserRequests::where('provider_id', $providerId)
            ->whereIn('status', ['IN_PROGRESS_MULTI', 'ACCEPTED_MULTI', 'STARTED'])
            ->sum('seats_booked');

        return max(0, $capacity - $activeSeats);
    }

    /**
     * Récupérer le tableau des départs (Style Aéroport)
     * GET /api/user/shared/departure-board
     */
    public function getDepartureBoard(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius' => 'nullable|numeric|min:1|max:50', // km
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $radius = $request->radius ?? 5; // Rayon par défaut 5km

        // 1. Trouver les arrêts proches
        $nearbyStops = PdpStop::select('pdp_stops.*')
            ->selectRaw("(6371 * acos( cos( radians(?) ) * cos( radians(latitude) ) * cos( radians(longitude) - radians(?) ) + sin( radians(?) ) * sin( radians(latitude) ) ) ) AS distance", [$latitude, $longitude, $latitude])
            ->having('distance', '<=', $radius)
            ->orderBy('distance')
            ->get();

        if ($nearbyStops->isEmpty()) {
            return response()->json(['departures' => []]);
        }

        $stopIds = $nearbyStops->pluck('id');
        $routeIds = $nearbyStops->pluck('pdp_route_id')->unique();

        // 2. Trouver les trajets actifs qui vont passer par ces arrêts
        // On cherche les rides dont le 'next_stop_id' est l'un de nos arrêts, ou qui sont sur la route avant nos arrêts
        $activeRides = \App\Models\ActiveSharedRide::whereIn('pdp_route_id', $routeIds)
            ->where('status', 'EN_ROUTE')
            ->with(['route', 'nextStop', 'provider.service'])
            ->get();

        $departures = [];

        foreach ($activeRides as $ride) {
            // Trouver l'arrêt pertinent pour l'utilisateur sur cette route
            $relevantStop = $nearbyStops->where('pdp_route_id', $ride->pdp_route_id)->first();
            
            if (!$relevantStop) continue;

            // Estimer le temps d'arrivée (ETA)
            // Simplification : Distance à vol d'oiseau / Vitesse moyenne (30km/h) + Traffic Factor
            $distanceToStop = Helper::haversineGreatCircleDistance(
                $ride->current_latitude,
                $ride->current_longitude,
                $relevantStop->latitude,
                $relevantStop->longitude
            ) / 1000;

            // Si le bus a déjà dépassé l'arrêt (approximatif via ordre), on l'ignore
            // Note: Il faudrait une logique plus robuste basée sur l'ordre des arrêts
            if ($ride->nextStop && $ride->nextStop->order > $relevantStop->order) {
                continue; 
            }

            $minutesToArrival = ceil(($distanceToStop / 30) * 60); // 30km/h avg
            
            // Statut "Aéroport"
            $status = 'ON TIME';
            $statusClass = 'text-success';
            
            if ($minutesToArrival <= 2) {
                $status = 'BOARDING';
                $statusClass = 'text-danger blink';
            } elseif ($minutesToArrival < 10) {
                $status = 'APPROACHING';
                $statusClass = 'text-warning';
            }

            $departures[] = [
                'flight_no' => 'BUS-' . substr($ride->provider->plate_number ?? 'XXX', -3), // Ex: BUS-123
                'destination' => $ride->route->end_location_name ?? 'Terminus',
                'time' => Carbon::now()->addMinutes($minutesToArrival)->format('H:i'),
                'eta_minutes' => $minutesToArrival,
                'status' => $status,
                'status_class' => $statusClass,
                'gate' => $relevantStop->name, // "Quai"
                'operator' => $ride->provider->first_name . ' ' . $ride->provider->last_name,
                'vehicle_type' => $ride->provider->service->service_type->name ?? 'Bus',
                'ride_id' => $ride->id,
                'route_id' => $ride->pdp_route_id
            ];
        }

        // Tri par heure de départ (le plus proche d'abord)
        usort($departures, function($a, $b) {
            return $a['eta_minutes'] <=> $b['eta_minutes'];
        });

        return response()->json([
            'location' => 'Départs autour de vous',
            'departures' => $departures
        ]);
    }

    private function calculateDetourMinutes(Provider $provider, array $segments, float $maxStopDistanceKm): ?float
    {
        $totalMinutes = 0;

        foreach ($segments as $segment) {
            $distanceToPickup = Helper::haversineGreatCircleDistance(
                $provider->latitude,
                $provider->longitude,
                (float) $segment['s_latitude'],
                (float) $segment['s_longitude']
            ) / 1000;

            if ($distanceToPickup > $maxStopDistanceKm) {
                return null;
            }

            $segmentDistance = Helper::haversineGreatCircleDistance(
                (float) $segment['s_latitude'],
                (float) $segment['s_longitude'],
                (float) $segment['d_latitude'],
                (float) $segment['d_longitude']
            ) / 1000;

            $totalMinutes += SharedTripService::estimateTravelMinutes($distanceToPickup);
            $totalMinutes += SharedTripService::estimateTravelMinutes($segmentDistance);
        }

        return $totalMinutes;
    }
}

