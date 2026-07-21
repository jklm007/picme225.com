<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserSubscriptionSchedule;
use App\Models\UserRequests;
use Carbon\Carbon;
use Log;
use DB;

/**
 * GenerateSubscriptionRides
 *
 * Generates SCHEDULED rides for active commute subscriptions.
 *
 * Run by cron every 15 minutes.
 * Looks 45–60 minutes ahead (T-60 window) to find schedules that need a ride today.
 *
 * IMPORTANT: Only generates rides for schedules that are:
 *  1. status = 'ACTIVE'
 *  2. expires_at > NOW()  ← prevents free rides on expired/unpaid subscriptions
 *  3. Current day is in active_days
 *  4. Pickup time is within the look-ahead window
 */
class GenerateSubscriptionRides extends Command
{
    protected $signature   = 'rides:generate-subscription';
    protected $description = 'Génère les courses planifiées (T-60 min) pour les abonnements de trajets récurrents actifs.';

    public function handle(): void
    {
        $now    = Carbon::now();
        $targetTimeStartStr = $now->copy()->addMinutes(45)->format('H:i');
        $targetTimeEndStr   = $now->copy()->addMinutes(60)->format('H:i');
        $currentDay         = strtoupper($now->format('D')); // MON, TUE, etc.

        // ── Fetch ACTIVE and VALID schedules only ──────────────────────────────
        $schedules = UserSubscriptionSchedule::where('status', 'ACTIVE')
            ->where('expires_at', '>', $now)           // ← KEY FIX: expired = no ride
            ->get();

        $this->info("[GenerateSubscriptionRides] {$schedules->count()} plannings actifs trouvés.");
        $generatedCount = 0;

        foreach ($schedules as $schedule) {
            // ── Day filter ──────────────────────────────────────────────────
            $activeDays = $schedule->active_days ?? [];
            if (! in_array($currentDay, $activeDays)) {
                continue;
            }

            // ── Pickup time window check ────────────────────────────────────
            $pickupHi = substr($schedule->pickup_time ?? '', 0, 5);
            if ($pickupHi >= $targetTimeStartStr && $pickupHi <= $targetTimeEndStr) {
                $scheduledAt = $now->format('Y-m-d') . ' ' . $pickupHi . ':00';
                if ($this->createRide($schedule, $scheduledAt, 'aller')) {
                    $generatedCount++;
                }
            }

            // ── Return trip window check ────────────────────────────────────
            if ($schedule->return_time) {
                $returnHi = substr($schedule->return_time, 0, 5);
                if ($returnHi >= $targetTimeStartStr && $returnHi <= $targetTimeEndStr) {
                    $scheduledAt = $now->format('Y-m-d') . ' ' . $returnHi . ':00';
                    if ($this->createRide($schedule, $scheduledAt, 'retour')) {
                        $generatedCount++;
                    }
                }
            }
        }

        Log::info("[GenerateSubscriptionRides] {$generatedCount} course(s) générée(s) pour T-60.");
        $this->info("[GenerateSubscriptionRides] {$generatedCount} course(s) générée(s).");
    }

    /**
     * Create a single SCHEDULED ride from a subscription schedule.
     * Returns true if the ride was created, false if it already existed.
     */
    private function createRide(UserSubscriptionSchedule $schedule, string $scheduledAt, string $direction): bool
    {
        // ── Duplicate guard ─────────────────────────────────────────────────
        $exists = UserRequests::where('user_id', $schedule->user_id)
            ->where('status', 'SCHEDULED')
            ->where('schedule_at', $scheduledAt)
            ->where('is_subscription_trip', 1)
            ->exists();

        if ($exists) {
            return false;
        }

        // ── Determine origin / destination based on direction ───────────────
        if ($direction === 'aller') {
            [$sLat, $sLng, $sAddr] = [$schedule->s_lat, $schedule->s_lng, $schedule->s_address];
            [$dLat, $dLng, $dAddr] = [$schedule->d_lat, $schedule->d_lng, $schedule->d_address];
        } else {
            // Return trip: invert origin and destination
            [$sLat, $sLng, $sAddr] = [$schedule->d_lat, $schedule->d_lng, $schedule->d_address];
            [$dLat, $dLng, $dAddr] = [$schedule->s_lat, $schedule->s_lng, $schedule->s_address];
        }

        // ── Use stored OSRM distance (already computed at subscription time) ─
        $distanceKm = $schedule->distance_km ?? 5;

        $ride = new UserRequests();
        $ride->booking_id           = 'PICME-SUB-' . time() . rand(100, 999);
        $ride->user_id              = $schedule->user_id;
        $ride->service_type_id      = $schedule->service_id;
        $ride->status               = 'SCHEDULED';
        $ride->payment_mode         = $schedule->payment_mode ?? 'WALLET';
        $ride->s_address            = $sAddr;
        $ride->s_latitude           = $sLat;
        $ride->s_longitude          = $sLng;
        $ride->d_address            = $dAddr;
        $ride->d_latitude           = $dLat;
        $ride->d_longitude          = $dLng;
        $ride->schedule_at          = $scheduledAt;
        $ride->distance             = $distanceKm;
        $ride->current_provider_id  = 0;
        $ride->otp                  = (string) rand(1000, 9999);
        $ride->route_key            = '';
        $ride->package_id           = 0;
        $ride->use_wallet           = ($ride->payment_mode === 'WALLET') ? 1 : 0;
        $ride->is_subscription_trip = 1;
        $ride->save();

        Log::info("[GenerateSubscriptionRides] Course #{$ride->id} · User #{$schedule->user_id} · {$direction} · {$scheduledAt}");
        return true;
    }
}
