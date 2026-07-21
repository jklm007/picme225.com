<?php

namespace App\Http\Controllers\ProviderResources;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use App\Models\ProviderBonus;
use Carbon\Carbon;

class BonusController extends Controller
{
    /**
     * Obtenir l'historique des bonus du chauffeur
     */
    public function index(Request $request)
    {
        $provider = Auth::user();
        
        $perPage = $request->get('per_page', 20);
        
        $bonuses = ProviderBonus::where('provider_id', $provider->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
        
        // Statistiques globales
        $stats = [
            'total_earned' => $provider->total_bonus_earned,
            'total_earned_cfa' => $provider->total_bonus_earned * 1000,
            'this_month' => ProviderBonus::where('provider_id', $provider->id)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('amount'),
            'this_month_cfa' => ProviderBonus::where('provider_id', $provider->id)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('amount') * 1000,
            'by_type' => ProviderBonus::where('provider_id', $provider->id)
                ->selectRaw('bonus_type, SUM(amount) as total_eco, SUM(amount * 1000) as total_cfa, COUNT(*) as count')
                ->groupBy('bonus_type')
                ->get()
                ->map(function($item) {
                    return [
                        'type' => $item->bonus_type,
                        'total_eco' => (float) $item->total_eco,
                        'total_cfa' => (float) $item->total_cfa,
                        'count' => $item->count
                    ];
                })
        ];
        
        return response()->json([
            'bonuses' => $bonuses,
            'stats' => $stats,
            'current_tier' => $provider->current_tier,
            'total_rides' => $provider->total_rides_lifetime,
            'consecutive_days' => $provider->consecutive_days_active
        ]);
    }
    
    /**
     * Obtenir les statistiques détaillées
     */
    public function stats(Request $request)
    {
        $provider = Auth::user();
        
        // Stats par période
        $last7Days = ProviderBonus::where('provider_id', $provider->id)
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->sum('amount');
        
        $last30Days = ProviderBonus::where('provider_id', $provider->id)
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->sum('amount');
        
        $thisYear = ProviderBonus::where('provider_id', $provider->id)
            ->whereYear('created_at', now()->year)
            ->sum('amount');
        
        // Évolution mensuelle (6 derniers mois)
        $monthlyEvolution = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $amount = ProviderBonus::where('provider_id', $provider->id)
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->sum('amount');
            
            $monthlyEvolution[] = [
                'month' => $date->format('Y-m'),
                'month_name' => $date->translatedFormat('F Y'),
                'amount_eco' => (float) $amount,
                'amount_cfa' => (float) ($amount * 1000)
            ];
        }
        
        // Top 3 types de bonus
        $topBonusTypes = ProviderBonus::where('provider_id', $provider->id)
            ->selectRaw('bonus_type, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('bonus_type')
            ->orderByDesc('total')
            ->limit(3)
            ->get()
            ->map(function($item) {
                return [
                    'type' => $item->bonus_type,
                    'total_eco' => (float) $item->total,
                    'total_cfa' => (float) ($item->total * 1000),
                    'count' => $item->count
                ];
            });
        
        return response()->json([
            'last_7_days' => [
                'eco' => (float) $last7Days,
                'cfa' => (float) ($last7Days * 1000)
            ],
            'last_30_days' => [
                'eco' => (float) $last30Days,
                'cfa' => (float) ($last30Days * 1000)
            ],
            'this_year' => [
                'eco' => (float) $thisYear,
                'cfa' => (float) ($thisYear * 1000)
            ],
            'monthly_evolution' => $monthlyEvolution,
            'top_bonus_types' => $topBonusTypes
        ]);
    }
    
    /**
     * Obtenir les achievements/milestones
     */
    public function achievements(Request $request)
    {
        $provider = Auth::user();
        
        // Milestones débloqués
        $unlockedMilestones = ProviderBonus::where('provider_id', $provider->id)
            ->where('bonus_type', 'MILESTONE')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($bonus) {
                return [
                    'trigger' => $bonus->trigger,
                    'amount_eco' => (float) $bonus->amount,
                    'amount_cfa' => (float) ($bonus->amount * 1000),
                    'unlocked_at' => $bonus->created_at->toDateTimeString()
                ];
            });
        
        // Progression vers prochain milestone
        $totalRides = $provider->total_rides_lifetime;
        $milestones = [100, 500, 1000, 5000, 10000];
        $nextMilestone = null;
        
        foreach ($milestones as $milestone) {
            if ($totalRides < $milestone) {
                $nextMilestone = [
                    'rides_required' => $milestone,
                    'rides_current' => $totalRides,
                    'rides_remaining' => $milestone - $totalRides,
                    'progress_percent' => round(($totalRides / $milestone) * 100, 2)
                ];
                break;
            }
        }
        
        // Streak actuel
        $streakInfo = [
            'current_days' => $provider->consecutive_days_active,
            'next_bonus_at' => null
        ];
        
        if ($provider->consecutive_days_active < 7) {
            $streakInfo['next_bonus_at'] = 7;
            $streakInfo['days_remaining'] = 7 - $provider->consecutive_days_active;
        } elseif ($provider->consecutive_days_active < 30) {
            $streakInfo['next_bonus_at'] = 30;
            $streakInfo['days_remaining'] = 30 - $provider->consecutive_days_active;
        } elseif ($provider->consecutive_days_active < 90) {
            $streakInfo['next_bonus_at'] = 90;
            $streakInfo['days_remaining'] = 90 - $provider->consecutive_days_active;
        }
        
        return response()->json([
            'current_tier' => $provider->current_tier,
            'total_rides' => $totalRides,
            'unlocked_milestones' => $unlockedMilestones,
            'next_milestone' => $nextMilestone,
            'streak' => $streakInfo
        ]);
    }
}
