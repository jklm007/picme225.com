<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Provider;
use App\Services\BonusCalculatorService;
use Carbon\Carbon;
use Log;

class CalculateMonthlyBonuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bonuses:calculate-monthly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculer et attribuer les bonus mensuels (ancienneté, croissance, zéro annulation, top performer)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Calcul des bonus mensuels...');
        
        $bonusService = new BonusCalculatorService();
        
        // Récupérer tous les providers actifs avec abonnement
        $providers = Provider::whereNotNull('subscription_plan_id')
            ->where('status', 'approved')
            ->with('subscriptionPlan')
            ->get();
        
        $totalBonuses = 0;
        $providersProcessed = 0;
        
        foreach ($providers as $provider) {
            // Ignorer les GRATUIT
            if (!$provider->subscriptionPlan || $provider->subscriptionPlan->name == 'GRATUIT') {
                continue;
            }
            
            $this->info("Traitement Provider #{$provider->id} ({$provider->first_name} {$provider->last_name})");
            
            // 1. Bonus Ancienneté
            $seniorityBonus = $bonusService->calculateSeniorityBonus($provider);
            if ($seniorityBonus > 0) {
                $this->line("  ✓ Ancienneté: {$seniorityBonus} ECO");
                $totalBonuses += $seniorityBonus;
            }
            
            // 2. Bonus Croissance
            $growthBonus = $bonusService->calculateGrowthBonus($provider);
            if ($growthBonus > 0) {
                $this->line("  ✓ Croissance: {$growthBonus} ECO");
                $totalBonuses += $growthBonus;
            }
            
            // 3. Bonus Zéro Annulation
            $zeroCancelBonus = $bonusService->calculateZeroCancellationBonus($provider);
            if ($zeroCancelBonus > 0) {
                $this->line("  ✓ Zéro Annulation: {$zeroCancelBonus} ECO");
                $totalBonuses += $zeroCancelBonus;
            }
            
            $providersProcessed++;
        }
        
        // 4. Top Performers (après avoir traité tous les providers)
        $this->info("\nCalcul des Top Performers...");
        $topPerformerBonuses = $bonusService->calculateTopPerformerBonuses();
        $totalBonuses += $topPerformerBonuses;
        
        $this->info("\n✅ Terminé!");
        $this->info("Providers traités: {$providersProcessed}");
        $this->info("Total bonus distribués: {$totalBonuses} ECO (" . ($totalBonuses * 1000) . " CFA)");
        
        Log::info("Monthly bonuses calculated", [
            'providers_processed' => $providersProcessed,
            'total_bonuses_eco' => $totalBonuses,
            'total_bonuses_cfa' => $totalBonuses * 1000
        ]);
        
        return 0;
    }
}
