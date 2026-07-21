<?php

namespace App\Services;

use App\Models\Fleet;
use DB;
use Log;

class FleetPrepaidService
{
    /**
     * Recharge prepaid balance for a fleet.
     */
    public function recharge(Fleet $fleet, $amount, $paymentMethod, $paymentReference)
    {
        try {
            DB::beginTransaction();

            $balanceBefore = $fleet->prepaid_balance;
            $fleet->increment('prepaid_balance', $amount);
            $balanceAfter = $fleet->prepaid_balance;

            // Log transaction
            DB::table('fleet_prepaid_transactions')->insert([
                'fleet_id' => $fleet->id,
                'type' => 'RECHARGE',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'payment_method' => $paymentMethod,
                'payment_reference' => $paymentReference,
                'description' => "Recharge de crédits via {$paymentMethod}",
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return [
                'success' => true,
                'new_balance' => $balanceAfter,
                'message' => "Recharge de {$amount} FCFA effectuée avec succès"
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Fleet Prepaid Recharge Error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Erreur lors de la recharge'
            ];
        }
    }

    /**
     * Deduct from prepaid balance (when agent makes a cash sale).
     */
    public function deduct(Fleet $fleet, $amount, $reference, $description)
    {
        if ($fleet->prepaid_balance < $amount) {
            return [
                'success' => false,
                'error' => 'Solde prépayé insuffisant. Veuillez recharger.',
                'current_balance' => $fleet->prepaid_balance
            ];
        }

        try {
            DB::beginTransaction();

            $balanceBefore = $fleet->prepaid_balance;
            $fleet->decrement('prepaid_balance', $amount);
            $balanceAfter = $fleet->prepaid_balance;

            // Log transaction
            DB::table('fleet_prepaid_transactions')->insert([
                'fleet_id' => $fleet->id,
                'type' => 'DEDUCTION',
                'amount' => -$amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference' => $reference,
                'description' => $description,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Check if balance is below threshold
            if ($balanceAfter < $fleet->prepaid_threshold) {
                $this->sendLowBalanceAlert($fleet);

                // Auto-recharge if enabled
                if ($fleet->auto_recharge_enabled) {
                    $this->triggerAutoRecharge($fleet);
                }
            }

            DB::commit();

            return [
                'success' => true,
                'new_balance' => $balanceAfter,
                'warning' => $balanceAfter < $fleet->prepaid_threshold ? 'Solde faible' : null
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Fleet Prepaid Deduction Error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Erreur lors de la déduction'
            ];
        }
    }

    /**
     * Check if fleet has sufficient prepaid balance.
     */
    public function hasSufficientBalance(Fleet $fleet, $amount)
    {
        return $fleet->prepaid_balance >= $amount;
    }

    /**
     * Send low balance alert.
     */
    private function sendLowBalanceAlert(Fleet $fleet)
    {
        // TODO: Send notification (SMS, Email, Push)
        Log::info("Low balance alert for Fleet {$fleet->id}: {$fleet->prepaid_balance} FCFA");
    }

    /**
     * Trigger auto-recharge (if configured).
     */
    private function triggerAutoRecharge(Fleet $fleet)
    {
        // TODO: Integrate with Mobile Money API for automatic recharge
        Log::info("Auto-recharge triggered for Fleet {$fleet->id}: {$fleet->auto_recharge_amount} FCFA");
    }

    /**
     * Determine if a fleet can perform a transaction based on its mode and debt status.
     * 
     * @param Fleet $fleet
     * @param float $amount The amount of the transaction
     * @return array ['allowed' => boolean, 'reason' => string]
     */
    public function canPerformOperation(Fleet $fleet, $amount)
    {
        // 1. Managed Mode (Prepaid) - Strict balance check
        if ($fleet->financial_mode === 'MANAGED') {
            if ($fleet->prepaid_balance < $amount) {
                return [
                    'allowed' => false,
                    'reason' => 'Solde prépayé insuffisant. Veuillez recharger votre compte.'
                ];
            }
            return ['allowed' => true];
        }

        // 2. Autonomous Mode (Postpaid) - Debt check
        if ($fleet->financial_mode === 'AUTONOMOUS') {
            // Restriction after 2 unpaid months (as requested by user)
            if ($fleet->unpaid_months_count >= 2) {
                // If restricted, we force them into prepaid logic
                if ($fleet->prepaid_balance < $amount) {
                    return [
                        'allowed' => false,
                        'reason' => "Transaction bloquée : Vous avez {$fleet->unpaid_months_count} mois d'impayés. Veuillez régulariser votre situation ou recharger votre crédit de secours."
                    ];
                }
            }

            // Manual hard block if restricted flag is set
            if ($fleet->is_restricted) {
                return [
                    'allowed' => false,
                    'reason' => 'Votre accès aux ventes est temporairement suspendu par l\'administration.'
                ];
            }

            return ['allowed' => true];
        }

        return ['allowed' => true]; // Default
    }

    /**
     * Get transaction history.
     */
    public function getTransactionHistory(Fleet $fleet, $limit = 50)
    {
        return DB::table('fleet_prepaid_transactions')
            ->where('fleet_id', $fleet->id)
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get();
    }
}
