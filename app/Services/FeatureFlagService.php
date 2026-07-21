<?php

namespace App\Services;

use App\Models\FeatureFlag;
use App\Models\FleetCapacitySnapshot;
use App\Models\Provider;
use App\Models\UserRequests;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FeatureFlagService
{
    /**
     * Check whether a feature flag is enabled, with a 60-second cache.
     *
     * @param  string   $key
     * @param  int|null $serviceId
     * @return bool
     */
    public function isEnabled(string $key, ?int $serviceId = null): bool
    {
        $cacheKey = FeatureFlag::cacheKey($key, $serviceId);

        return (bool) Cache::remember($cacheKey, 60, function () use ($key, $serviceId) {
            $query = FeatureFlag::where('key', $key);

            if ($serviceId !== null) {
                $query->where('service_id', $serviceId);
            }

            $flag = $query->first();

            return $flag ? $flag->is_enabled : false;
        });
    }

    /**
     * Enable a feature flag and clear its cache.
     *
     * @param  string $key
     * @return void
     */
    public function enable(string $key): void
    {
        $flags = FeatureFlag::where('key', $key)->get();

        foreach ($flags as $flag) {
            $flag->is_enabled = true;
            $flag->save();

            Cache::forget(FeatureFlag::cacheKey($key));
            Cache::forget(FeatureFlag::cacheKey($key, $flag->service_id));

            Log::info('FeatureFlagService: flag enabled', [
                'key'        => $key,
                'service_id' => $flag->service_id,
            ]);
        }
    }

    /**
     * Disable a feature flag and clear its cache.
     *
     * @param  string $key
     * @return void
     */
    public function disable(string $key): void
    {
        $flags = FeatureFlag::where('key', $key)->get();

        foreach ($flags as $flag) {
            $flag->is_enabled = false;
            $flag->save();

            Cache::forget(FeatureFlag::cacheKey($key));
            Cache::forget(FeatureFlag::cacheKey($key, $flag->service_id));

            Log::info('FeatureFlagService: flag disabled', [
                'key'        => $key,
                'service_id' => $flag->service_id,
            ]);
        }
    }

    /**
     * Take a fleet capacity snapshot for the given service and zone.
     *
     * - Counts online (active) providers matching the service.
     * - Counts active requests (SEARCHING, ACCEPTED, STARTED) in the last 30 minutes.
     * - Computes utilization_rate = active_requests / max(1, online_providers).
     * - Persists and returns a FleetCapacitySnapshot record.
     *
     * @param  int    $serviceId
     * @param  string $zone
     * @return FleetCapacitySnapshot
     */
    public function snapshot(int $serviceId, string $zone = '*'): FleetCapacitySnapshot
    {
        // Count online providers for this service.
        $onlineProviders = Provider::where('status', 'approved')
            ->whereHas('service', function ($q) use ($serviceId) {
                $q->where('id', $serviceId);
            })
            ->count();

        // Count active requests created in the last 30 minutes.
        $activeRequests = UserRequests::whereIn('status', ['SEARCHING', 'ACCEPTED', 'STARTED'])
            ->where('created_at', '>=', Carbon::now()->subMinutes(30))
            ->count();

        // Compute utilization rate.
        $utilizationRate = $activeRequests / max(1, $onlineProviders);

        $snapshot = FleetCapacitySnapshot::create([
            'service_id'       => $serviceId,
            'zone'             => $zone,
            'online_providers' => $onlineProviders,
            'active_requests'  => $activeRequests,
            'utilization_rate' => $utilizationRate,
            'avg_wait_time_min'=> null,
            'threshold_met'    => false,
            'snapped_at'       => Carbon::now(),
        ]);

        Log::info('FeatureFlagService: snapshot taken', [
            'service_id'       => $serviceId,
            'zone'             => $zone,
            'online_providers' => $onlineProviders,
            'active_requests'  => $activeRequests,
            'utilization_rate' => $utilizationRate,
        ]);

        return $snapshot;
    }

    /**
     * Automatically activate a service flag if online_providers meets the minimum threshold.
     *
     * Takes a snapshot, and if online_providers >= $minProviders, enables the flag
     * `service_{serviceId}_enabled`. Returns true if the flag was activated, false otherwise.
     *
     * @param  int $serviceId
     * @param  int $minProviders
     * @return bool
     */
    public function autoActivate(int $serviceId, int $minProviders = 5): bool
    {
        $snapshot = $this->snapshot($serviceId);

        if ($snapshot->online_providers >= $minProviders) {
            $flagKey = 'service_' . $serviceId . '_enabled';
            $this->enable($flagKey);

            Log::info('FeatureFlagService: autoActivate triggered', [
                'service_id'      => $serviceId,
                'flag_key'        => $flagKey,
                'online_providers'=> $snapshot->online_providers,
                'min_providers'   => $minProviders,
            ]);

            return true;
        }

        return false;
    }
}
