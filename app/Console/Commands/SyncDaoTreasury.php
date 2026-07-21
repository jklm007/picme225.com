<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SyncDaoTreasury extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dao:sync-treasury';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronise les frais de la trésorerie DAO vers la blockchain Polygon';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Début de la synchronisation DAO Treasury...');

        // Récupérer les paiements non synchronisés avec des frais de trésorerie ou TVA
        $payments = \App\Models\UserRequestPayment::where('is_synced_to_chain', false)
            ->where(function($query) {
                $query->where('dao_treasury_fee', '>', 0)
                      ->orWhere('tva_fee', '>', 0);
            })
            ->limit(100) 
            ->get();

        if ($payments->isEmpty()) {
            $this->info('Aucun nouveau frais à synchroniser.');
            return;
        }

        $totalFee = $payments->sum('dao_treasury_fee');
        $totalTva = $payments->sum('tva_fee');
        $batchId = uniqid('batch_');

        $this->info("Total Trésorerie : $totalFee ECO | Total TVA : $totalTva ECO ($batchId)");

        try {
            $web3 = new \App\Services\Web3Service();
            $result = $web3->syncTreasuryFees($totalFee, $totalTva, $batchId);

            if ($result['status'] === 'SUCCESS') {
                foreach ($payments as $payment) {
                    $payment->update([
                        'is_synced_to_chain' => true,
                        'blockchain_tx_hash' => $result['transaction_hash']
                    ]);
                }
                $this->info("Synchronisation réussie ! Transaction : " . $result['transaction_hash']);
            } else {
                $this->error('Échec de la synchronisation blockchain.');
            }
        } catch (\Exception $e) {
            $this->error('Erreur lors de la synchronisation : ' . $e->getMessage());
        }
    }
}
