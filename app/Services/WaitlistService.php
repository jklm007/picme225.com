<?php

namespace App\Services;

use App\Models\ServiceWaitlist;
use App\Models\Service;
use App\Http\Controllers\SendPushNotification;

class WaitlistService
{
    /**
     * Add a user to the waitlist for a given service.
     *
     * Premium subscribers (subscription_plan_id not null) are boosted ahead
     * of non-subscribers by counting only non-subscriber waiting entries to
     * compute their position.
     *
     * @param  int    $userId
     * @param  int    $serviceId
     * @param  array  $data     Optional extra fields (latitude, longitude, zone,
     *                           service_type_id, preferred_time, subscription_plan_id)
     * @return ServiceWaitlist
     */
    public function join(int $userId, int $serviceId, array $data = []): ServiceWaitlist
    {
        // Return existing entry if the user is already waiting
        $existing = ServiceWaitlist::where('user_id', $userId)
            ->where('service_id', $serviceId)
            ->where('status', 'waiting')
            ->first();

        if ($existing) {
            return $existing;
        }

        $isPremium = !empty($data['subscription_plan_id']);

        if ($isPremium) {
            // Premium users are inserted before non-subscribers.
            // Count how many non-subscriber waiting entries exist → that becomes
            // the new position (pushing the user to just before non-subscribers).
            $nonSubscriberCount = ServiceWaitlist::where('service_id', $serviceId)
                ->where('status', 'waiting')
                ->whereNull('subscription_plan_id')
                ->count();

            // Total waiting entries (premium + non-premium)
            $totalWaiting = ServiceWaitlist::where('service_id', $serviceId)
                ->where('status', 'waiting')
                ->count();

            // Position = (total - non-subscriber count) + 1, i.e. right before non-subs
            $position = ($totalWaiting - $nonSubscriberCount) + 1;
        } else {
            // Non-premium: append at the end
            $position = ServiceWaitlist::where('service_id', $serviceId)
                ->where('status', 'waiting')
                ->count() + 1;
        }

        $entry = ServiceWaitlist::create(array_merge([
            'user_id'    => $userId,
            'service_id' => $serviceId,
            'position'   => $position,
            'status'     => 'waiting',
        ], $data));

        // Recompute all positions to ensure consistency after insertion
        $this->recomputePositions($serviceId);

        // Reload to get the recalculated position
        $entry->refresh();

        // Send push notification
        $serviceName = optional(Service::find($serviceId))->name ?? 'Service';
        (new SendPushNotification())->WaitlistJoined($userId, $serviceName, $entry->position);

        return $entry;
    }

    /**
     * Remove a user from the waitlist by setting their status to 'cancelled',
     * then recompute positions for the remaining waiting users.
     *
     * @param  int  $userId
     * @param  int  $serviceId
     * @return bool
     */
    public function leave(int $userId, int $serviceId): bool
    {
        $entry = ServiceWaitlist::where('user_id', $userId)
            ->where('service_id', $serviceId)
            ->where('status', 'waiting')
            ->first();

        if (!$entry) {
            return false;
        }

        $entry->update(['status' => 'cancelled']);

        $this->recomputePositions($serviceId);

        return true;
    }

    /**
     * Notify the top $limit waiting users that the service is now available.
     * Sets their status to 'notified' and records notified_at timestamp.
     *
     * @param  int  $serviceId
     * @param  int  $limit
     * @return int  Number of users notified
     */
    public function notify(int $serviceId, int $limit = 10): int
    {
        $entries = ServiceWaitlist::where('service_id', $serviceId)
            ->where('status', 'waiting')
            ->orderBy('position', 'asc')
            ->limit($limit)
            ->get();

        $serviceName = optional(Service::find($serviceId))->name ?? 'Service';
        $push = new SendPushNotification();

        foreach ($entries as $entry) {
            $entry->update([
                'status'      => 'notified',
                'notified_at' => now(),
            ]);

            $push->WaitlistAvailable($entry->user_id, $serviceName);
        }

        return $entries->count();
    }

    /**
     * Recompute sequential positions for all 'waiting' entries of a service.
     * Ordering: premium subscribers (subscription_plan_id NOT NULL) first,
     * then by created_at ASC within each group.
     *
     * @param  int  $serviceId
     * @return void
     */
    public function recomputePositions(int $serviceId): void
    {
        $entries = ServiceWaitlist::where('service_id', $serviceId)
            ->where('status', 'waiting')
            ->orderByRaw('CASE WHEN subscription_plan_id IS NOT NULL THEN 0 ELSE 1 END ASC')
            ->orderBy('created_at', 'asc')
            ->get();

        foreach ($entries as $index => $entry) {
            $entry->update(['position' => $index + 1]);
        }
    }

    /**
     * Return the current waitlist status for a user on a given service,
     * including an estimated wait time in minutes.
     *
     * Returns null if the user has no active waitlist entry.
     *
     * @param  int  $userId
     * @param  int  $serviceId
     * @return array|null
     */
    public function status(int $userId, int $serviceId): ?array
    {
        $entry = ServiceWaitlist::where('user_id', $userId)
            ->where('service_id', $serviceId)
            ->whereIn('status', ['waiting', 'notified'])
            ->latest()
            ->first();

        if (!$entry) {
            return null;
        }

        // Rough estimate: assume each position takes ~5 minutes
        $estimatedWaitMin = ($entry->position > 0) ? ($entry->position - 1) * 5 : 0;

        return array_merge($entry->toArray(), [
            'estimated_wait_min' => $estimatedWaitMin,
        ]);
    }
}
