<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Provider;
use App\Services\InsuranceManagerService;
use Carbon\Carbon;

class SyncInsuranceRestitution extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dao:distribute-insurance-bonus {--month= : Month in YYYY-MM format} {--simulate : Run without updating wallets}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Analyze safe driving records and distribute virtual ECO restitution bonuses to drivers with no claims.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $insuranceService = new InsuranceManagerService();
        $monthStr = $this->option('month') ?: Carbon::now()->subMonth()->format('Y-m');
        $month = Carbon::createFromFormat('Y-m', $monthStr);
        $simulate = $this->option('simulate');

        $this->info("Starting Insurance Restitution for $monthStr" . ($simulate ? " (SIMULATION)" : ""));

        $providers = Provider::where('status', 'approved')->get();
        $totalBonus = 0;
        $count = 0;

        foreach ($providers as $provider) {
            $bonus = $insuranceService->calculateRestitution($provider, $month);

            if ($bonus > 0) {
                $this->line("Provider #{$provider->id} ({$provider->first_name}): Bonus $bonus ECO");
                
                if (!$simulate) {
                    $provider->eco_wallet_balance += $bonus;
                    $provider->save();
                    
                    // Log the transaction in the database if a transaction table exists
                    // \App\WalletTransaction::create([...]);
                }

                $totalBonus += $bonus;
                $count++;
            }
        }

        $this->info("Completed! Distributed total of $totalBonus ECO to $count drivers.");
    }
}
