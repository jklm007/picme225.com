<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserSubscriptionSchedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * CheckExpiredSchedules
 *
 * Scans all ACTIVE transport commute schedules and marks as EXPIRED those
 * whose `expires_at` is in the past. This prevents the GenerateSubscriptionRides
 * cron from generating free rides for subscriptions that have not been renewed.
 *
 * Also scans users whose marketplace subscription has expired and clears
 * marketplace_plan_id / marketplace_plan_expires_at.
 *
 * Run: php artisan subscription:check-expired-schedules
 * Schedule: daily or every hour.
 */
class CheckExpiredSchedules extends Command
{
    protected $signature   = 'subscription:check-expired-schedules';
    protected $description = 'Expire les plannings de trajet récurrent et les abonnements Marketplace dont la validité est dépassée.';

    public function handle(): void
    {
        $now = Carbon::now();

        // ── 1. Expire transport commute schedules ─────────────────────────────
        $expiredSchedules = UserSubscriptionSchedule::where('status', 'ACTIVE')
            ->where('expires_at', '<', $now)
            ->get();

        $scheduleCount = 0;
        foreach ($expiredSchedules as $schedule) {
            $schedule->update(['status' => 'EXPIRED']);
            $scheduleCount++;
            Log::info("[CheckExpiredSchedules] Schedule #{$schedule->id} → EXPIRED (User #{$schedule->user_id})");
        }

        // ── 2. Clear expired marketplace subscriptions on users ───────────────
        $expiredMarketplaceUsers = User::whereNotNull('marketplace_plan_id')
            ->where('marketplace_plan_expires_at', '<', $now)
            ->get();

        $marketplaceCount = 0;
        foreach ($expiredMarketplaceUsers as $user) {
            $user->update([
                'marketplace_plan_id'       => null,
                'marketplace_plan_expires_at' => null,
            ]);
            $marketplaceCount++;
            Log::info("[CheckExpiredSchedules] Marketplace plan cleared for User #{$user->id}");
        }

        // ── 3. Also run legacy provider subscriptions check ───────────────────
        $expiredProviders = \App\Models\Provider::where('subscription_expires_at', '<', $now)
            ->where('subscription_level', '!=', 'none')
            ->get();

        $providerCount = 0;
        foreach ($expiredProviders as $provider) {
            $provider->update([
                'subscription_level'      => 'none',
                'subscription_plan_id'    => null,
                'subscription_expires_at' => null,
            ]);
            $providerCount++;
            Log::info("[CheckExpiredSchedules] Provider #{$provider->id} downgraded to FREE.");
        }

        $this->info("Résultat :");
        $this->info("  · {$scheduleCount} planning(s) trajet expiré(s)");
        $this->info("  · {$marketplaceCount} abonnement(s) Marketplace expiré(s)");
        $this->info("  · {$providerCount} abonnement(s) chauffeur rétrogradé(s)");

        Log::info("[CheckExpiredSchedules] Done. Schedules={$scheduleCount}, Marketplace={$marketplaceCount}, Providers={$providerCount}");
    }
}
