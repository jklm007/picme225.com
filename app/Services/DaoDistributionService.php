<?php

namespace App\Services;

use App\Models\UserRequestPayment;
use App\Models\Provider;
use App\Models\EcoTokenTransaction;
use Setting;
use Log;

class DaoDistributionService
{
    /**
     * Get commission rate based on provider subscription level and service type.
     * Supports both percentage and fixed amount.
     * 
     * @param Provider $provider
     * @param float $tripAmount Total trip amount
     * @param int|null $serviceId The service category ID (Standard, Rental, etc.)
     * @return array ['type' => 'percentage|fixed', 'value' => float, 'amount' => float]
     */
    public function getProviderCommission(Provider $provider, $tripAmount, $serviceId = null)
    {
        // Sécurité : Si c'est un trajet de Location (ID 3) ou de Voyage (ID 4),
        // les abonnements Eco/Pro n'ont pas de réduction. En revanche, le niveau GOLD bénéficie d'une commission réduite (paramétrable dans le panel admin, par défaut 10%).
        if (in_array($serviceId, [3, 4])) {
            $level = $provider->subscription_level ?? 'none';
            if ($level === 'gold') {
                $commissionValue = (float) \Setting::get('gold_rental_voyage_commission', 10); // Réduction spéciale GOLD paramétrable
            } else {
                $commissionValue = 20; // Par défaut
                if ($provider->service && $provider->service->service_type) {
                    $commissionValue = (float) ($provider->service->service_type->commission_percentage ?? 20);
                }
            }
            return [
                'type' => 'percentage',
                'value' => $commissionValue,
                'amount' => round(($tripAmount * $commissionValue) / 100, 2)
            ];
        }

        $subscription_enabled = \Setting::get('subscription_enabled', '1') === '1';
        $plan = $subscription_enabled ? $provider->subscriptionPlan : null;
        $level = $subscription_enabled ? ($provider->subscription_level ?? 'none') : 'none';

        $commissionType = 'percentage';
        $commissionValue = $this->getDefaultCommissionValue($level);

        if ($plan && $serviceId) {
            // Check for specific commission for this plan and service
            $specificCommission = \App\Models\SubscriptionPlanServiceCommission::where('subscription_plan_id', $plan->id)
                ->where('service_id', $serviceId)
                ->first();

            if ($specificCommission) {
                $commissionType = $specificCommission->commission_type;
                $commissionValue = (float) $specificCommission->commission_value;
            } else {
                // Fallback to plan defaults
                $commissionType = $plan->commission_type;
                $commissionValue = (float) $plan->commission_value;
            }
        } else {
            // Legacy/Setting fallback
            $commissionType = Setting::get("dao_commission_{$level}_type", 'percentage');
            $commissionValue = (float) Setting::get("dao_commission_{$level}_value", $this->getDefaultCommissionValue($level));
        }

        // Calcul du montant final
        if ($commissionType === 'fixed') {
            $amount = $commissionValue; // Montant fixe en CFA
        } else {
            $amount = ($tripAmount * $commissionValue) / 100; // Pourcentage
        }

        return [
            'type' => $commissionType,
            'value' => $commissionValue,
            'amount' => $amount
        ];
    }

    /**
     * Get percentage rate for legacy calculations in TripController.
     */
    public function getProviderCommissionRate(Provider $provider, $serviceId = null)
    {
        $res = $this->getProviderCommission($provider, 0, $serviceId);
        return $res['type'] === 'percentage' ? $res['value'] : 0;
    }

    /**
     * Helper to find Service ID from request
     */
    public function getServiceIdFromRequest($userRequest)
    {
        // Voyage (Outstation)
        if ($userRequest->service_type->outstation_price > 0 && ($userRequest->round_trip || $userRequest->method == 'outstation')) {
            return 4; // ID Voyage réel dans la DB
        }

        // Location (Rental)
        if ($userRequest->package_id != 0 || $userRequest->rental_hours != null) {
            return 3; // ID Location réel dans la DB
        }

        // Urgence (Ambulance)
        if ($userRequest->service_type->ambulance == 1) {
            return 5; // ID Urgence réel dans la DB
        }

        // Partage (Shared Ride)
        if ($userRequest->service_type->sharing_type != 'NONE') {
            return 6; // ID Partage réel dans la DB
        }

        // Default to Standard (Taxi)
        return 1;
    }

    /**
     * Get default commission values for backward compatibility.
     */
    private function getDefaultCommissionValue($level)
    {
        return match ($level) {
            'gold' => 0,       // 0% pour Gold par défaut
            'pro' => 5,        // 5%
            'eco' => 10,       // 10%
            'standard' => 15,  // 15%
            'none' => 25,      // 25%
            default => 25,
        };
    }

    /**
     * Calculate and apply DAO fees to a payment record.
     * All fees are now calculated from the commission to avoid negative treasury.
     *
     * @param UserRequestPayment $payment
     * @return UserRequestPayment
     */
    public function applyDaoFees(UserRequestPayment $payment)
    {
        $totalCommission = $payment->provider_commission; // Commission totale prélevée
        $request = $payment->request;
        $provider = Provider::find($request->provider_id);

        // Récupération des pourcentages depuis les paramètres (DAO Governance)
        // Tous les pourcentages sont maintenant calculés SUR LA COMMISSION
        $tvaPercent = (float) Setting::get('dao_tva_percentage', 18);
        $insurancePercent = (float) Setting::get('dao_insurance_percentage', 15);
        $bonusPercent = (float) Setting::get('dao_bonus_percentage', 5); // 5% pour les bonus

        $syndicatePercent = (float) Setting::get('dao_syndicate_percentage', 10);
        $cooperativePercent = (float) Setting::get('dao_cooperative_percentage', 10);

        // Fusion Syndicat + Coopérative si affilié
        if ($provider && $provider->is_syndicated) {
            $syndicateCoopMergedPercent = 15; // 15% au total si affilié
            $payment->syndicate_fee = ($totalCommission * $syndicateCoopMergedPercent) / 100;
            $payment->cooperative_fee = 0; // Fusionné dans syndicate_fee
        } else {
            $payment->syndicate_fee = ($totalCommission * $syndicatePercent) / 100;
            $payment->cooperative_fee = ($totalCommission * $cooperativePercent) / 100;
        }

        // Calcul des autres montants
        $payment->tva_fee = ($totalCommission * $tvaPercent) / 100;
        $payment->insurance_fee = ($totalCommission * $insurancePercent) / 100;
        $payment->bonus_fee = ($totalCommission * $bonusPercent) / 100;

        // La trésorerie DAO reçoit le reste (toujours positif ou nul)
        $totalDistributed = $payment->tva_fee + $payment->insurance_fee +
            $payment->syndicate_fee + $payment->cooperative_fee + $payment->bonus_fee;

        $payment->dao_treasury_fee = max(0, $totalCommission - $totalDistributed);

        // Vérification de cohérence
        $totalFees = $payment->tva_fee + $payment->insurance_fee +
            $payment->syndicate_fee + $payment->cooperative_fee +
            $payment->bonus_fee + $payment->dao_treasury_fee;

        if (abs($totalFees - $totalCommission) > 0.01) {
            Log::warning("DAO Fee calculation mismatch", [
                'commission' => $totalCommission,
                'total_fees' => $totalFees,
                'difference' => $totalCommission - $totalFees
            ]);
        }

        $payment->save();

        // --- NEW: Revenue Distribution to Partner (Fleet Owner / Syndicate / Recruiter) ---
        if ($provider) {
            $partner = $provider->partner;
            if (!$partner && $provider->fleet) {
                // Fallback: resolve partner via legacy fleet link
                $fleet = \App\Models\Fleet::find($provider->fleet);
                if ($fleet && $fleet->user_id) {
                    $partner = \App\Models\Partner::where('user_id', $fleet->user_id)->first();
                }
            }

            if ($partner && $partner->user) {
                $partnerSharePercent = (float) $partner->getCommissionRule('trip_share_percent', 100); // 100% of syndicate_fee by default
                $partnerShare = ($payment->syndicate_fee * $partnerSharePercent) / 100;

                if ($partnerShare > 0) {
                    $partnerUser = $partner->user;
                    $partnerUser->increment('wallet_balance', $partnerShare);

                    // Log to unified WalletPassbook
                    \App\Models\WalletPassbook::create([
                        'user_id' => $partnerUser->id,
                        'partner_id' => $partner->id,
                        'amount' => $partnerShare,
                        'status' => 'CREDITED',
                        'via' => 'FLEET_TRIP_SHARE',
                        'description' => 'Com. partenaire pour la course #' . $request->booking_id . ' (Chauffeur: ' . $provider->first_name . ')',
                        'reference_id' => 'RIDE_' . $request->id,
                    ]);
                }
            }
        }

        Log::info("DAO Fees Applied", [
            'payment_id' => $payment->id,
            'commission' => $totalCommission,
            'tva' => $payment->tva_fee,
            'insurance' => $payment->insurance_fee,
            'syndicate' => $payment->syndicate_fee,
            'cooperative' => $payment->cooperative_fee,
            'bonus' => $payment->bonus_fee,
            'treasury' => $payment->dao_treasury_fee
        ]);

        // Gestion de l'exonération TVA si active
        $taxExemptionService = new \App\Services\TaxExemptionService();
        if ($taxExemptionService->isExemptionActive()) {
            $taxExemptionService->allocateVirtualTva($payment);
            Log::info("TVA virtuelle allouée pendant exonération", [
                'payment_id' => $payment->id,
                'virtual_tva' => $payment->tva_fee
            ]);
        }

        return $payment;
    }

    /**
     * Deduct commission from driver ECO balance if payment mode is CASH.
     *
     * @param UserRequestPayment $payment
     * @return void
     */
    public function handleCashPayment(UserRequestPayment $payment)
    {
        $request = $payment->request;
        if ($request->payment_mode == 'CASH') {
            $provider = Provider::find($request->provider_id);

            // On récupère la commission ET le booking fee (frais plateforme)
            $commission = $payment->provider_commission;
            $booking_fee = $payment->booking_fee ?? 0;
            $total_to_deduct_cfa = $commission + $booking_fee;
            $total_to_deduct_eco = $total_to_deduct_cfa / 1000.0; // Conversion CFA vers ECO

            if ($provider) {
                // Déduction du solde ECO du chauffeur (Commission + Frais Plateforme)
                $provider->decrement('eco_wallet_balance', $total_to_deduct_eco);

                // Enregistrement de la transaction ECO (en ECO)
                EcoTokenTransaction::create([
                    'user_id' => $provider->id,
                    'wallet_address' => $provider->wallet_address ?? 'SYSTEM_CASH',
                    'type' => 'CASH_COMMISSION_DEDUCTION',
                    'amount' => -$total_to_deduct_eco,
                    'transaction_hash' => 'CASH_' . time() . '_' . $payment->id,
                    'status' => 'CONFIRMED',
                    'reference_type' => 'UserRequestPayment',
                    'reference_id' => $payment->id,
                ]);

                // Enregistrement dans le nouveau Wallet Chauffeur (historique gardé en CFA pour l'affichage)
                \App\Models\ProviderWallet::create([
                    'provider_id' => $provider->id,
                    'amount' => -$total_to_deduct_cfa,
                    'transaction_id' => 'TRIP_' . $request->id,
                    'transaction_desc' => 'Commission pour la course #' . $request->booking_id,
                    'type' => 'DEBIT',
                    'balance' => $provider->eco_wallet_balance,
                ]);

                Log::info("DAO: Total of $total_to_deduct (Comm: $commission + Fee: $booking_fee) deducted from Provider {$provider->id} for Cash Trip {$request->id}");

                // Calculer et attribuer les bonus (uniquement pour abonnés)
                $bonusService = new \App\Services\BonusCalculatorService();
                $bonusAmount = $bonusService->calculateRideBonuses($request);

                if ($bonusAmount > 0) {
                    Log::info("Bonus calculated: {$bonusAmount} CFA for Provider {$provider->id} on Ride {$request->id}");
                }
            }
        }
    }

    /**
     * Distribute revenue for Logistics/Package delivery.
     *
     * @param \App\Models\PackageRequest $package
     * @return void
     */
    public function distributeLogisticsRevenue(\App\Models\PackageRequest $package)
    {
        if ($package->status !== 'DELIVERED' || $package->price <= 0) {
            return;
        }

        $totalAmount = $package->price;
        $platformFeePercent = (float) Setting::get('logistics_commission_percent', 10);
        $platformFee = ($totalAmount * $platformFeePercent) / 100;
        $companyShare = $totalAmount - $platformFee;

        // 1. If Interurban Freight, credit the Partner (Fleet Owner / Station Agent / Company Owner)
        if ($package->type === 'STATION_FREIGHT' && $package->interurban_company_id) {
            $company = \App\Models\InterurbanCompany::find($package->interurban_company_id);
            if ($company) {
                $partner = null;
                if ($company->fleet_id) {
                    $fleet = \App\Models\Fleet::find($company->fleet_id);
                    if ($fleet && $fleet->user_id) {
                        $partner = \App\Models\Partner::where('user_id', $fleet->user_id)->first();
                    }
                }

                if ($partner && $partner->user) {
                    $partnerUser = $partner->user;
                    $partnerUser->increment('wallet_balance', $companyShare);

                    // Log to unified WalletPassbook
                    \App\Models\WalletPassbook::create([
                        'user_id' => $partnerUser->id,
                        'partner_id' => $partner->id,
                        'amount' => $companyShare,
                        'status' => 'CREDITED',
                        'via' => 'LOGISTICS_REVENUE',
                        'description' => 'Gain Logistique (Colis #' . $package->tracking_code . ')',
                        'reference_id' => 'PKG_' . $package->id,
                    ]);

                    Log::info("Logistics Revenue Distributed to Partner: " . $partner->id);
                }
            }
        }

        // 2. If Instant Delivery (Driver-led), credit the driver (Already handled by standard trip flow if using UserRequest,
        // but since this is a separate table, we might need a dedicated flow here or link them)
        // For now, focusing on the requested "Station to Station" revenue.
    }
}
