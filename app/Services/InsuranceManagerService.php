<?php

namespace App\Services;

use App\Models\Provider;
use App\Models\InsuranceClaim;
use App\Models\UserRequestPayment;
use Carbon\Carbon;
use DB;

class InsuranceManagerService
{
    /**
     * Get the total virtual pool of insurance fees collected.
     * 
     * @return float
     */
    public function getTotalPool()
    {
        return UserRequestPayment::sum('insurance_fee');
    }

    /**
     * Calculate the potential restitution (bonus) for a driver for a specific month.
     * Logic: If 0 claims were filed and approved, return X% of the insurance they paid.
     * 
     * @param Provider $provider
     * @param Carbon $month
     * @return float
     */
    public function calculateRestitution(Provider $provider, Carbon $month)
    {
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        // Check for approved claims in that month
        $hasClaims = InsuranceClaim::where('provider_id', $provider->id)
            ->whereBetween('incident_date', [$start, $end])
            ->where('status', 'APPROVED')
            ->exists();

        if ($hasClaims) {
            return 0;
        }

        // Calculate total insurance fee paid by this provider in that month
        $totalPaid = UserRequestPayment::whereHas('request', function($query) use ($provider) {
                $query->where('provider_id', $provider->id);
            })
            ->whereBetween('created_at', [$start, $end])
            ->sum('insurance_fee');

        // Restore 20% of paid insurance as a "No-Claim Bonus" (Policy can be adjusted by DAO)
        $restitutionPercentage = \Setting::get('dao_insurance_restitution_percentage', 20);
        
        return ($totalPaid * $restitutionPercentage) / 100;
    }

    /**
     * Process an insurance claim approval.
     * Approved amount is added to the driver's virtual ECO wallet.
     * 
     * @param InsuranceClaim $claim
     * @param float $approvedAmount
     * @param string $comment
     * @return bool
     */
    public function approveClaim(InsuranceClaim $claim, $approvedAmount, $comment = '')
    {
        return DB::transaction(function() use ($claim, $approvedAmount, $comment) {
            $claim->update([
                'status' => 'APPROVED',
                'amount_approved' => $approvedAmount,
                'admin_comment' => $comment
            ]);

            $provider = $claim->provider;
            $provider->eco_wallet_balance += $approvedAmount;
            $provider->save();

            // Log transaction
            // Potentially create an entries in a 'wallet_transactions' table if one exists
            
            return true;
        });
    }
}
