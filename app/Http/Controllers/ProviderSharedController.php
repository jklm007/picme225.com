<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SendPushNotification;
use App\Models\ProviderService;
use App\Models\RequestFilter;
use App\Models\ServiceType;
use App\Models\UserRequests;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProviderSharedController extends Controller
{
    public function accept(Request $request, $requestId): JsonResponse
    {
        $provider = Auth::guard('providerapi')->user();

        if (!$provider) {
            return response()->json(['error' => trans('api.provider.unauthenticated')], 401);
        }

        $userRequest = UserRequests::findOrFail($requestId);

        if (!in_array($userRequest->status, ['MATCHING', 'SEARCHING_MULTI', 'ACCEPTED_MULTI'])) {
            return response()->json(['error' => trans('api.ride.cannot_accept')], 422);
        }

        $serviceType = ServiceType::findOrFail($userRequest->service_type_id);
        $availableSeats = $this->availableSeatsForProvider($provider->id, $serviceType->capacity);

        if ($availableSeats < $userRequest->seats_booked) {
            return response()->json(['error' => trans('api.ride.capacity_exceeded')], 422);
        }

        $providerService = ProviderService::where('provider_id', $provider->id)
            ->where('service_type_id', $serviceType->id)
            ->first();

        if (!$providerService) {
            return response()->json(['error' => trans('api.provider.service_not_enabled')], 422);
        }

        $userRequest->provider_id = $provider->id;
        $userRequest->current_provider_id = $provider->id;
        $userRequest->status = 'ACCEPTED_MULTI';
        $userRequest->assigned_at = Carbon::now();
        $userRequest->save();

        $providerService->update(['status' => 'riding']);

        RequestFilter::where('request_id', $userRequest->id)
            ->where('provider_id', '!=', $provider->id)
            ->delete();

        $notifier = new SendPushNotification();
        $notifier->RideAccepted($userRequest);
        $userRequest->passengers->each(function ($passenger) use ($notifier, $userRequest) {
            $notifier->RideAccepted($userRequest, $passenger->user_id);
        });

        return response()->json(['message' => trans('api.ride.accepted')]);
    }

    public function reject(Request $request, $requestId): JsonResponse
    {
        $provider = Auth::guard('providerapi')->user();

        if (!$provider) {
            return response()->json(['error' => trans('api.provider.unauthenticated')], 401);
        }

        RequestFilter::where('request_id', $requestId)
            ->where('provider_id', $provider->id)
            ->delete();

        return response()->json(['message' => trans('api.ride.rejected')]);
    }

    private function availableSeatsForProvider(int $providerId, int $capacity): int
    {
        $activeSeats = UserRequests::where('provider_id', $providerId)
            ->whereIn('status', ['IN_PROGRESS_MULTI', 'ACCEPTED_MULTI', 'STARTED'])
            ->sum('seats_booked');

        return max(0, $capacity - $activeSeats);
    }
}

