<?php

namespace App\Services;

use App\Models\UserRequests;
use App\Models\Provider;
use App\Models\DriverAssignmentLog;
use App\Models\RequestFilter;
use App\Http\Controllers\SendPushNotification;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Setting;

class DispatcherHybridService
{
    /**
     * Assign a driver manually.
     */
    public function assignManual($requestId, $providerId, $dispatcherId)
    {
        $request = UserRequests::findOrFail($requestId);
        $provider = Provider::findOrFail($providerId);

        // Update Request
        $request->provider_id = $provider->id;
        $request->status = 'STARTED'; // Or ACCEPTED depending on flow
        $request->current_provider_id = $provider->id;
        $request->assigned_at = Carbon::now();
        $request->save();

        // Update Provider Status
        $provider->service()->update(['status' => 'riding']);

        // Log Assignment
        DriverAssignmentLog::create([
            'user_request_id' => $request->id,
            'dispatcher_id' => $dispatcherId,
            'assignment_mode' => 'MANUAL',
            'provider_id' => $provider->id,
            'status' => 'ACCEPTED'
        ]);

        // Generate Ticket if Intercommunal
        if ($request->service_type && $request->service_type->is_intercommunal) {
            (new TicketService())->generate($request);
        }

        // Notify Driver
        (new SendPushNotification)->IncomingRequest($provider->id, $request);

        return $request;
    }

    /**
     * Broadcast to nearby drivers.
     */
    public function broadcastToDrivers($requestId, $dispatcherId, $radius = 10)
    {
        $request = UserRequests::with('service_type')->findOrFail($requestId);
        
        // Find Drivers
        $latitude = $request->s_latitude;
        $longitude = $request->s_longitude;
        $serviceTypeId = $request->service_type_id;

        $activeProviders = \App\Models\ProviderService::AvailableServiceProvider($serviceTypeId)
                ->get()
                ->pluck('provider_id');

        // Calculate Estimated Price & Commission
        // Note: This is a simplified estimation. Ideally, use the same logic as estimateFare.
        $estimatedPrice = $request->estimated_fare ?? 0; 
        if ($estimatedPrice == 0 && $request->service_type) {
             // Fallback estimation if not set
             $estimatedPrice = $request->service_type->fixed + ($request->distance * $request->service_type->price);
        }
        
        $commissionPercentage = $request->service_type->commission_percentage ?? 15;

        $providers = Provider::whereIn('id', $activeProviders)
            ->where('status', 'approved')
            ->whereRaw("(1.609344 * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $radius")
            ->get();

        // FILTER: Tokenomics (Solvabilité)
        $providers = $providers->filter(function ($provider) use ($estimatedPrice) {
            return $provider->canAffordCommission($estimatedPrice);
        });

        if ($providers->count() == 0) {
            return ['success' => false, 'message' => 'No providers found (or insufficient ECO balance)'];
        }

        // SORT: Priority to Wealthiest (ECO Balance DESC) then Distance
        $providers = $providers->sortByDesc('eco_wallet_balance');

        // Create Filters for all providers
        foreach ($providers as $provider) {
            RequestFilter::create([
                'request_id' => $request->id,
                'provider_id' => $provider->id,
                'status' => 0 // Pending
            ]);
            
            // Notify each driver
            (new SendPushNotification)->IncomingRequest($provider->id, $request);
        }

        // Dispatch offline SMS for the broadcasted providers
        try {
            app(\App\Services\OfflineSmsDispatchService::class)->dispatchToOfflineProviders(
                $providers->pluck('id')->toArray(), 
                $request
            );
        } catch (\Exception $e) {
            Log::error("Error dispatching offline SMS in broadcastToDrivers: " . $e->getMessage());
        }

        // Log Assignment Attempt
        DriverAssignmentLog::create([
            'user_request_id' => $request->id,
            'dispatcher_id' => $dispatcherId,
            'assignment_mode' => 'BROADCAST',
            'status' => 'INITIATED'
        ]);

        $request->status = 'SEARCHING';
        $request->save();

        return ['success' => true, 'count' => $providers->count()];
    }
}
