<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Provider;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ExpireEcoBonuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'eco:expire-bonuses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retire le bonus de lancement de 100 ECO si la date d\'expiration est passée.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Scanning for expired ECO bonuses...');

        $expiredProviders = Provider::whereNotNull('bonus_expires_at')
            ->where('bonus_expires_at', '<', Carbon::now())
            ->get();

        $count = 0;

        foreach ($expiredProviders as $provider) {
            // On retire le bonus (100 ECO)
            // On s'assure de ne pas mettre le solde en négatif juste à cause de ça (optionnel, mais plus propre)
            // Règle stricte demandée : "retire 100 ECO (si solde suffisant)"
            
            if ($provider->eco_wallet_balance >= 100) {
                $provider->decrement('eco_wallet_balance', 100);
                
                // On nullifie la date pour ne pas le refaire demain
                $provider->bonus_expires_at = null;
                $provider->save();
                
                $count++;
                Log::info("Bonus expired for Provider #{$provider->id}. 100 ECO removed.");
            } else {
                // Cas limite : Le chauffeur a dépensé son bonus.
                // On peut soit le mettre en négatif, soit retirer ce qui reste.
                // Ici, on retire ce qui reste et on nullifie.
                $remaining = $provider->eco_wallet_balance;
                $provider->eco_wallet_balance = 0;
                $provider->bonus_expires_at = null;
                $provider->save();
                
                Log::info("Bonus expired for Provider #{$provider->id}. {$remaining} ECO removed (Partial).");
            }
        }

        $this->info("Processed {$count} expired bonuses.");
    }
}
