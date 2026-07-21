<?php

namespace App\Services;

use App\Models\UserRequestPayment;
use App\Models\TaxExemptionFund;
use App\Models\TaxExemptionConfig;
use Carbon\Carbon;
use Setting;
use Log;

class TaxExemptionService
{
    /**
     * Vérifier si l'exonération est active
     */
    public function isExemptionActive()
    {
        $config = TaxExemptionConfig::where('is_active', true)
            ->where('start_date', '<=', Carbon::now())
            ->where('end_date', '>=', Carbon::now())
            ->first();
        
        return $config !== null;
    }
    
    /**
     * Obtenir la configuration active
     */
    public function getActiveConfig()
    {
        return TaxExemptionConfig::where('is_active', true)
            ->where('start_date', '<=', Carbon::now())
            ->where('end_date', '>=', Carbon::now())
            ->first();
    }
    
    /**
     * Allouer la TVA virtuelle selon la stratégie définie
     */
    public function allocateVirtualTva(UserRequestPayment $payment)
    {
        if (!$this->isExemptionActive()) {
            return; // Pas d'exonération active
        }
        
        $config = $this->getActiveConfig();
        $virtualTva = $payment->tva_fee; // Montant TVA qui aurait été payé
        
        // Récupérer les pourcentages d'allocation
        $allocations = json_decode($config->allocation_percentages, true);
        
        foreach ($allocations as $type => $percentage) {
            if ($percentage > 0) {
                $amount = ($virtualTva * $percentage) / 100;
                
                TaxExemptionFund::create([
                    'payment_id' => $payment->id,
                    'virtual_tva_amount' => $virtualTva,
                    'allocation_type' => $type,
                    'allocated_amount' => $amount,
                    'exemption_end_date' => $config->end_date,
                    'notes' => "Allocation automatique - {$percentage}%"
                ]);
                
                // Appliquer l'allocation selon le type
                $this->applyAllocation($type, $amount, $payment);
            }
        }
        
        Log::info("TVA virtuelle allouée", [
            'payment_id' => $payment->id,
            'virtual_tva' => $virtualTva,
            'allocations' => $allocations
        ]);
    }
    
    /**
     * Appliquer l'allocation selon le type
     */
    private function applyAllocation($type, $amount, $payment)
    {
        switch ($type) {
            case 'treasury_reserve':
                // Mettre en réserve pour payer la TVA après exonération
                // Peut être stocké dans un compte séparé ou un wallet dédié
                break;
                
            case 'driver_bonus':
                // Réduire la commission du chauffeur ou donner un bonus
                $provider = $payment->request->provider;
                if ($provider) {
                    $provider->increment('eco_wallet_balance', $amount);
                    Log::info("Bonus chauffeur TVA exonérée", [
                        'provider_id' => $provider->id,
                        'amount' => $amount
                    ]);
                }
                break;
                
            case 'platform_development':
                // Fonds pour développement technique
                // Peut être tracké dans un wallet dédié
                break;
                
            case 'marketing_growth':
                // Budget marketing
                break;
                
            case 'insurance_pool':
                // Renforcer le pool d'assurance
                break;
                
            case 'cooperative_fund':
                // Fonds coopératif
                break;
        }
    }
    
    /**
     * Obtenir le total des fonds par type d'allocation
     */
    public function getFundsByType()
    {
        return TaxExemptionFund::selectRaw('
            allocation_type,
            SUM(allocated_amount) as total_allocated,
            COUNT(*) as transaction_count
        ')
        ->groupBy('allocation_type')
        ->get();
    }
    
    /**
     * Obtenir le total économisé pendant l'exonération
     */
    public function getTotalSavings()
    {
        return TaxExemptionFund::sum('virtual_tva_amount');
    }
    
    /**
     * Calculer les jours restants d'exonération
     */
    public function getDaysRemaining()
    {
        $config = $this->getActiveConfig();
        
        if (!$config) {
            return 0;
        }
        
        return Carbon::now()->diffInDays($config->end_date, false);
    }
    
    /**
     * Générer un rapport de l'utilisation des fonds
     */
    public function generateReport()
    {
        $config = $this->getActiveConfig();
        
        if (!$config) {
            return null;
        }
        
        $fundsByType = $this->getFundsByType();
        $totalSavings = $this->getTotalSavings();
        $daysRemaining = $this->getDaysRemaining();
        
        return [
            'exemption_active' => true,
            'start_date' => $config->start_date,
            'end_date' => $config->end_date,
            'days_remaining' => $daysRemaining,
            'total_savings' => $totalSavings,
            'allocations' => $fundsByType,
            'allocation_percentages' => json_decode($config->allocation_percentages, true)
        ];
    }
}
