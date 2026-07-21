<?php

namespace App\Jobs;

use App\Http\Controllers\SendPushNotification;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SubscriptionExpiryReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     * Finds all users whose subscription expires within the next 3 days
     * and sends them a push notification reminder.
     */
    public function handle(): void
    {
        $now     = Carbon::now();
        $in3Days = Carbon::now()->addDays(3);

        $users = User::whereNotNull('subscription_plan_id')
            ->whereBetween('subscription_expires_at', [$now, $in3Days])
            ->get();

        $sentCount = 0;

        foreach ($users as $user) {
            try {
                /** @var SubscriptionPlan|null $plan */
                $plan = SubscriptionPlan::find($user->subscription_plan_id);

                if (! $plan) {
                    Log::warning('[SubscriptionExpiryReminderJob] Plan not found for user', [
                        'user_id'                => $user->id,
                        'subscription_plan_id'   => $user->subscription_plan_id,
                    ]);
                    continue;
                }

                $daysLeft = (int) $now->diffInDays($user->subscription_expires_at, false);
                // Clamp to at least 0 (should never be negative given our query, but defensive)
                $daysLeft = max(0, $daysLeft);

                (new SendPushNotification())->SubscriptionExpiringSoon(
                    $user->id,
                    $plan->name,
                    $daysLeft
                );

                $sentCount++;
            } catch (\Throwable $e) {
                Log::error('[SubscriptionExpiryReminderJob] Failed to send reminder for user', [
                    'user_id' => $user->id,
                    'error'   => $e->getMessage(),
                    'trace'   => $e->getTraceAsString(),
                ]);
            }
        }

        Log::info('[SubscriptionExpiryReminderJob] Subscription expiry reminders sent', [
            'total_users_found' => $users->count(),
            'reminders_sent'    => $sentCount,
        ]);
    }
}
