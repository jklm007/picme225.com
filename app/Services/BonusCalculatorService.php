<?php

namespace App\Services;

use App\Models\Provider;
use App\Models\UserRequests;
use App\Models\ProviderBonus;
use App\Models\EcoTokenTransaction;
use Carbon\Carbon;
use Log;

class BonusCalculatorService
{
    /**
     * Pourcentages de bonus par plan (pour atteindre 10% des commissions)
     */
    private function getBonusPercentage($planName)
    {
        $percentages = [
            'STANDARD' => 0,     // 0% - Aucun bonus
            'ECO' => 0.22,       // 22% de la commission (ex BASIC)
            'PRO' => 0.27,       // 27% de la commission (ex SILVER)
            'GOLD' => 0.32,      // 32% de la commission
        ];

        return $percentages[$planName] ?? 0;
    }

    /**
     * Calculer budget bonus disponible pour une course
     */
    private function getBonusBudget(Provider $provider, $commission)
    {
        $plan = $provider->subscriptionPlan;

        // STANDARD ou pas d'abonnement = AUCUN BONUS
        if (!$plan || $plan->name == 'STANDARD') {
            return 0;
        }

        $percentage = $this->getBonusPercentage($plan->name);

        // Budget en CFA
        return $commission * $percentage;
    }

    /**
     * Calculer tous les bonus pour une course terminée
     */
    public function calculateRideBonuses(UserRequests $ride)
    {
        $provider = $ride->provider;

        // Récupérer la commission prélevée
        $payment = $ride->payment;
        if (!$payment) {
            return 0;
        }

        $commission = $payment->provider_commission ?? 0;

        // Vérifier si éligible aux bonus
        $bonusBudget = $this->getBonusBudget($provider, $commission);

        if ($bonusBudget == 0) {
            Log::info("Provider {$provider->id} not eligible for bonuses (GRATUIT plan)");
            return 0;
        }

        $bonuses = [];

        // 1. Rating Excellence
        $ratingBonus = $this->calculateRatingBonus($provider, $ride);
        if ($ratingBonus > 0) {
            $bonuses['RATING_EXCELLENCE'] = $ratingBonus;
        }

        // 2. Acceptation Rapide
        $acceptanceBonus = $this->calculateAcceptanceBonus($ride);
        if ($acceptanceBonus > 0) {
            $bonuses['QUICK_ACCEPTANCE'] = $acceptanceBonus;
        }

        // 3. Ponctualité
        $punctualityBonus = $this->calculatePunctualityBonus($ride);
        if ($punctualityBonus > 0) {
            $bonuses['PUNCTUALITY'] = $punctualityBonus;
        }

        // 4. Peak Hours
        $peakBonus = $this->calculatePeakHoursBonus($ride);
        if ($peakBonus > 0) {
            $bonuses['PEAK_HOURS'] = $peakBonus;
        }

        // Calculer total demandé
        $totalRequested = array_sum($bonuses);

        // Plafonner au budget disponible
        if ($totalRequested > $bonusBudget) {
            // Appliquer prorata
            $ratio = $bonusBudget / $totalRequested;
            foreach ($bonuses as $type => $amount) {
                $bonuses[$type] = $amount * $ratio;
            }
            $totalBonus = $bonusBudget;

            Log::info("Bonuses capped: requested {$totalRequested} CFA, budget {$bonusBudget} CFA, ratio {$ratio}");
        } else {
            $totalBonus = $totalRequested;
        }

        // Créditer les bonus
        if ($totalBonus > 0) {
            $this->creditBonus($provider, $totalBonus, $bonuses, $ride->id);
        }

        // Mettre à jour les compteurs
        $this->updateProviderStats($provider, $ride);

        return $totalBonus;
    }

    /**
     * Bonus Rating Excellence (+2.5%, +1.5%, +0.8%)
     */
    private function calculateRatingBonus(Provider $provider, UserRequests $ride)
    {
        $rating = $provider->rating ?? 0;
        $rideAmount = $ride->payment->total ?? 0;

        if ($rating >= 4.9) {
            return $rideAmount * 0.025; // +2.5%
        } elseif ($rating >= 4.7) {
            return $rideAmount * 0.015; // +1.5%
        } elseif ($rating >= 4.5) {
            return $rideAmount * 0.008; // +0.8%
        }

        return 0;
    }

    /**
     * Bonus Acceptation Rapide (30 CFA, 15 CFA)
     */
    private function calculateAcceptanceBonus(UserRequests $ride)
    {
        $requestFilter = $ride->requestFilter;
        if (!$requestFilter)
            return 0;

        $acceptanceTime = $requestFilter->created_at->diffInSeconds($requestFilter->updated_at);

        if ($acceptanceTime <= 10) {
            return 30; // 30 CFA (0.03 ECO)
        } elseif ($acceptanceTime <= 20) {
            return 15; // 15 CFA (0.015 ECO)
        }

        return 0;
    }

    /**
     * Bonus Ponctualité (20 CFA)
     */
    private function calculatePunctualityBonus(UserRequests $ride)
    {
        if (!$ride->started_at || !$ride->assigned_at)
            return 0;

        $estimatedArrival = $ride->assigned_at->addMinutes($ride->estimated_time_arrival ?? 10);
        $actualArrival = $ride->started_at;

        if ($actualArrival <= $estimatedArrival) {
            return 20; // 20 CFA (0.02 ECO)
        }

        return 0;
    }

    /**
     * Bonus Peak Hours (+1.2% ou +1.8%)
     */
    private function calculatePeakHoursBonus(UserRequests $ride)
    {
        $hour = $ride->created_at->hour;
        $rideAmount = $ride->payment->total ?? 0;

        // Heures de pointe matin/soir (7h-9h, 17h-19h)
        if (($hour >= 7 && $hour < 9) || ($hour >= 17 && $hour < 19)) {
            return $rideAmount * 0.012; // +1.2%
        }

        // Heures de nuit (22h-6h)
        if ($hour >= 22 || $hour < 6) {
            return $rideAmount * 0.018; // +1.8%
        }

        return 0;
    }

    /**
     * Créditer les bonus au chauffeur
     */
    private function creditBonus($provider, $totalAmount, $bonuses, $rideId)
    {
        // Convertir CFA en ECO (1 ECO = 1,000 CFA)
        $totalEco = $totalAmount / 1000;

        // Créditer le wallet ECO
        $provider->increment('eco_wallet_balance', $totalEco);
        $provider->increment('total_bonus_earned', $totalEco);

        // Enregistrer transaction ECO globale
        EcoTokenTransaction::create([
            'user_id' => $provider->id,
            'type' => 'RIDE_BONUS',
            'amount' => $totalEco,
            'status' => 'CONFIRMED',
            'reference_type' => 'UserRequest',
            'reference_id' => $rideId
        ]);

        // Enregistrer détails de chaque bonus
        foreach ($bonuses as $type => $amountCfa) {
            $amountEco = $amountCfa / 1000;

            ProviderBonus::create([
                'provider_id' => $provider->id,
                'bonus_type' => $type,
                'amount' => $amountEco,
                'trigger' => $this->getTriggerLabel($type, $provider),
                'related_id' => $rideId,
                'related_type' => 'UserRequest',
                'status' => 'APPROVED',
                'paid_at' => now()
            ]);
        }

        Log::info("Bonus credited: {$totalEco} ECO ({$totalAmount} CFA) to Provider {$provider->id} for Ride {$rideId}");
    }

    /**
     * Obtenir le label du trigger pour le bonus
     */
    private function getTriggerLabel($type, $provider)
    {
        switch ($type) {
            case 'RATING_EXCELLENCE':
                $rating = $provider->rating ?? 0;
                if ($rating >= 4.9)
                    return 'rating_4.9';
                if ($rating >= 4.7)
                    return 'rating_4.7';
                if ($rating >= 4.5)
                    return 'rating_4.5';
                return 'rating';
            case 'QUICK_ACCEPTANCE':
                return 'quick_accept';
            case 'PUNCTUALITY':
                return 'on_time';
            case 'PEAK_HOURS':
                return 'peak_hours';
            default:
                return strtolower($type);
        }
    }

    /**
     * Mettre à jour les statistiques du provider
     */
    private function updateProviderStats($provider, $ride)
    {
        // Incrémenter le compteur de courses lifetime
        $provider->increment('total_rides_lifetime');

        // Mettre à jour le streak de jours consécutifs
        $this->updateStreak($provider);

        // Vérifier les milestones
        $this->checkMilestones($provider);
    }

    /**
     * Mettre à jour le streak de jours consécutifs actifs
     */
    private function updateStreak($provider)
    {
        $today = Carbon::today();
        $lastActive = $provider->last_active_date ? Carbon::parse($provider->last_active_date) : null;

        if (!$lastActive) {
            // Première course
            $provider->consecutive_days_active = 1;
            $provider->last_active_date = $today;
            $provider->save();
            return;
        }

        $daysDiff = $lastActive->diffInDays($today);

        if ($daysDiff == 0) {
            // Même jour, pas de changement
            return;
        } elseif ($daysDiff == 1) {
            // Jour consécutif
            $provider->consecutive_days_active += 1;
            $provider->last_active_date = $today;
            $provider->save();

            // Vérifier bonus streak
            $this->checkStreakBonuses($provider);
        } else {
            // Streak cassé
            $provider->consecutive_days_active = 1;
            $provider->last_active_date = $today;
            $provider->save();
        }
    }

    /**
     * Vérifier et attribuer bonus streak
     */
    private function checkStreakBonuses($provider)
    {
        $streak = $provider->consecutive_days_active;

        if ($streak == 7) {
            $this->creditManualBonus($provider, 0.2, 'STREAK', '7_days_streak');
        } elseif ($streak == 30) {
            $this->creditManualBonus($provider, 1.0, 'STREAK', '30_days_streak');
        } elseif ($streak == 90) {
            $this->creditManualBonus($provider, 3.0, 'STREAK', '90_days_streak');
        }
    }

    /**
     * Vérifier et débloquer milestones
     */
    private function checkMilestones($provider)
    {
        $totalRides = $provider->total_rides_lifetime;

        $milestones = [
            100 => ['tier' => 'BRONZE', 'bonus' => 0.5],
            500 => ['tier' => 'SILVER', 'bonus' => 2.0],
            1000 => ['tier' => 'GOLD', 'bonus' => 5.0],
            5000 => ['tier' => 'PLATINUM', 'bonus' => 20.0],
            10000 => ['tier' => 'DIAMOND', 'bonus' => 50.0],
        ];

        foreach ($milestones as $threshold => $data) {
            if ($totalRides == $threshold) {
                // Mettre à jour tier
                $provider->current_tier = $data['tier'];
                $provider->save();

                // Attribuer bonus
                $this->creditManualBonus($provider, $data['bonus'], 'MILESTONE', "{$threshold}_rides");

                Log::info("Milestone unlocked: {$data['tier']} for Provider {$provider->id}");
            }
        }
    }

    /**
     * Créditer un bonus manuel (streak, milestone, etc.)
     */
    private function creditManualBonus($provider, $amountEco, $type, $trigger)
    {
        // Créditer wallet
        $provider->increment('eco_wallet_balance', $amountEco);
        $provider->increment('total_bonus_earned', $amountEco);

        // Enregistrer transaction
        EcoTokenTransaction::create([
            'user_id' => $provider->id,
            'type' => 'BONUS_' . $type,
            'amount' => $amountEco,
            'status' => 'CONFIRMED'
        ]);

        // Enregistrer bonus
        ProviderBonus::create([
            'provider_id' => $provider->id,
            'bonus_type' => $type,
            'amount' => $amountEco,
            'trigger' => $trigger,
            'status' => 'APPROVED',
            'paid_at' => now()
        ]);

        Log::info("Manual bonus credited: {$amountEco} ECO to Provider {$provider->id} for {$trigger}");
    }

    /**
     * ========================================
     * BONUS MENSUELS
     * ========================================
     */

    /**
     * Bonus Ancienneté (calculé mensuellement)
     */
    public function calculateSeniorityBonus(Provider $provider)
    {
        if (!$provider->created_at)
            return 0;

        $yearsActive = $provider->created_at->diffInYears(now());

        $bonuses = [
            1 => 0.3,   // 1 an = 300 CFA
            2 => 0.7,   // 2 ans = 700 CFA
            3 => 1.5,   // 3 ans = 1,500 CFA
            5 => 3.0,   // 5 ans = 3,000 CFA
        ];

        $bonusAmount = 0;
        foreach ($bonuses as $years => $amount) {
            if ($yearsActive >= $years) {
                $bonusAmount = $amount;
            }
        }

        if ($bonusAmount > 0) {
            $this->creditManualBonus($provider, $bonusAmount, 'SENIORITY', "{$yearsActive}_years");
        }

        return $bonusAmount;
    }

    /**
     * Bonus Croissance (calculé mensuellement)
     */
    public function calculateGrowthBonus(Provider $provider)
    {
        // Comparer le nombre de courses ce mois vs mois précédent
        $currentMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        $ridesThisMonth = UserRequests::where('provider_id', $provider->id)
            ->where('status', 'COMPLETED')
            ->whereBetween('created_at', [$currentMonth, Carbon::now()])
            ->count();

        $ridesLastMonth = UserRequests::where('provider_id', $provider->id)
            ->where('status', 'COMPLETED')
            ->whereBetween('created_at', [$lastMonth, $currentMonth])
            ->count();

        if ($ridesLastMonth == 0)
            return 0;

        $growthPercent = (($ridesThisMonth - $ridesLastMonth) / $ridesLastMonth) * 100;

        $bonusAmount = 0;
        if ($growthPercent >= 100) {
            $bonusAmount = 3.0; // +100% = 3,000 CFA
        } elseif ($growthPercent >= 50) {
            $bonusAmount = 1.5; // +50% = 1,500 CFA
        } elseif ($growthPercent >= 20) {
            $bonusAmount = 0.5; // +20% = 500 CFA
        }

        if ($bonusAmount > 0) {
            $this->creditManualBonus($provider, $bonusAmount, 'GROWTH', "growth_{$growthPercent}%");
        }

        return $bonusAmount;
    }

    /**
     * Bonus Zéro Annulation (calculé mensuellement)
     */
    public function calculateZeroCancellationBonus(Provider $provider)
    {
        $bonusAmount = 0;

        // Vérifier 30 jours
        if ($provider->cancellations_last_30_days == 0) {
            $bonusAmount = 0.75; // 750 CFA
            $this->creditManualBonus($provider, $bonusAmount, 'ZERO_CANCELLATION', '30_days_zero');
        }
        // Sinon vérifier 7 jours
        elseif ($provider->cancellations_last_7_days == 0) {
            $bonusAmount = 0.15; // 150 CFA
            $this->creditManualBonus($provider, $bonusAmount, 'ZERO_CANCELLATION', '7_days_zero');
        }

        // Reset compteurs pour le mois prochain
        $provider->cancellations_last_7_days = 0;
        $provider->cancellations_last_30_days = 0;
        $provider->save();

        return $bonusAmount;
    }

    /**
     * Bonus Top Performers (calculé mensuellement pour tous les providers)
     */
    public function calculateTopPerformerBonuses()
    {
        // Récupérer tous les providers avec abonnement actif
        $providers = Provider::whereNotNull('subscription_plan_id')
            ->where('status', 'approved')
            ->with('subscriptionPlan')
            ->get();

        // Filtrer les GRATUIT
        $providers = $providers->filter(function ($p) {
            return $p->subscriptionPlan && $p->subscriptionPlan->name != 'STANDARD';
        });

        if ($providers->isEmpty())
            return 0;

        // Trier par rating
        $sortedByRating = $providers->sortByDesc('rating');

        $totalBonuses = 0;
        $count = $sortedByRating->count();

        // Top 1%
        $top1PercentCount = max(1, ceil($count * 0.01));
        $top1Percent = $sortedByRating->take($top1PercentCount);

        foreach ($top1Percent as $provider) {
            $this->creditManualBonus($provider, 10.0, 'TOP_PERFORMER', 'top_1_percent');
            $totalBonuses += 10.0;
        }

        // Top 5% (excluant top 1%)
        $top5PercentCount = max(1, ceil($count * 0.05));
        $top5Percent = $sortedByRating->skip($top1PercentCount)->take($top5PercentCount - $top1PercentCount);

        foreach ($top5Percent as $provider) {
            $this->creditManualBonus($provider, 5.0, 'TOP_PERFORMER', 'top_5_percent');
            $totalBonuses += 5.0;
        }

        // Top 10% (excluant top 5%)
        $top10PercentCount = max(1, ceil($count * 0.10));
        $top10Percent = $sortedByRating->skip($top5PercentCount)->take($top10PercentCount - $top5PercentCount);

        foreach ($top10Percent as $provider) {
            $this->creditManualBonus($provider, 2.0, 'TOP_PERFORMER', 'top_10_percent');
            $totalBonuses += 2.0;
        }

        return $totalBonuses;
    }
}
