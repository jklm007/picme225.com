<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AutoReleaseEscrow extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'escrow:auto-release';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto release escrow funds for completed jobs after 72h without client dispute.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limitDate = now()->subHours(72);
        
        $quotes = \App\Models\ChatQuote::where('status', 'AWAITING_CLIENT_CONFIRMATION')
                    ->where('updated_at', '<=', $limitDate)
                    ->get();

        $count = 0;
        foreach ($quotes as $quote) {
            \DB::transaction(function () use ($quote) {
                $commissionPercent = 15;
                $commissionAmount = ($quote->amount * $commissionPercent) / 100;
                $sellerCredit     = $quote->amount - $commissionAmount;

                $seller = \App\Models\Provider::find($quote->provider_id);
                if ($seller) {
                    $seller->increment('wallet_balance', $sellerCredit);
                }

                $quote->status = 'AUTO_RELEASED';
                $quote->save();

                // TODO: Enqueue Gateway Payout Robot Task here
            });
            $count++;

            // Optional: push notification
            try {
                $pushData = [
                    'type' => 'QUOTE_AUTO_RELEASED',
                    'title' => 'Paiement libéré automatiquement',
                    'message' => 'Le délai de 72h est écoulé. Les fonds sont ajoutés à votre portefeuille.',
                    'quote_id' => $quote->id
                ];
                (new \App\Http\Controllers\SendPushNotification())->sendPushToProvider($quote->provider_id, $pushData);
            } catch (\Exception $e) {}
        }

        $this->info("AutoReleaseEscrow: Processed {$count} quotes.");
    }
}
