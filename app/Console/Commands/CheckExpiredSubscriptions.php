<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Provider;
use Carbon\Carbon;

class CheckExpiredSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscription:check-expiry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and downgrade expired driver subscriptions to the default NONE level.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiredProviders = Provider::where('subscription_expires_at', '<', Carbon::now())
            ->where('subscription_level', '!=', 'none')
            ->get();

        $count = 0;
        foreach ($expiredProviders as $provider) {
            $provider->update([
                'subscription_level' => 'none',
                'subscription_plan_id' => null,
                'subscription_expires_at' => null,
            ]);
            $count++;
            
            $this->line("Downgraded Provider #{$provider->id} due to expiry.");
            
            // Potentially send a push notification here
            // (new SendPushNotification)->SubscriptionExpired($provider->id);
        }

        if ($count > 0) {
            $this->info("Successfully downgraded $count expired subscriptions.");
        } else {
            $this->info("No expired subscriptions found.");
        }
    }
}
