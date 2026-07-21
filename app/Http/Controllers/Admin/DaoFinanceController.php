<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserRequestPayment;
use App\Models\Provider;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Setting;

class DaoFinanceController extends Controller
{
    /**
     * Dashboard principal de répartition financière DAO
     */
    public function dashboard(Request $request)
    {
        $period = $request->get('period', 'today'); // today, week, month, year, all
        
        // Déterminer la plage de dates
        $query = UserRequestPayment::query();
        
        switch ($period) {
            case 'today':
                $query->whereDate('created_at', Carbon::today());
                break;
            case 'week':
                $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereMonth('created_at', Carbon::now()->month)
                      ->whereYear('created_at', Carbon::now()->year);
                break;
            case 'year':
                $query->whereYear('created_at', Carbon::now()->year);
                break;
            // 'all' ne filtre pas
        }
        
        // Agrégation des données financières
        $financialData = $query->selectRaw('
            COUNT(*) as total_trips,
            SUM(total) as total_revenue,
            SUM(provider_commission) as total_commission,
            SUM(tva_fee) as total_tva,
            SUM(insurance_fee) as total_insurance,
            SUM(syndicate_fee) as total_syndicate,
            SUM(cooperative_fee) as total_cooperative,
            SUM(dao_treasury_fee) as total_treasury
        ')->first();
        
        // Répartition par niveau d'abonnement
        $commissionByLevel = DB::table('user_request_payments')
            ->join('user_requests', 'user_request_payments.request_id', '=', 'user_requests.id')
            ->join('providers', 'user_requests.provider_id', '=', 'providers.id')
            ->when($period !== 'all', function($q) use ($period) {
                switch ($period) {
                    case 'today':
                        return $q->whereDate('user_request_payments.created_at', Carbon::today());
                    case 'week':
                        return $q->whereBetween('user_request_payments.created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                    case 'month':
                        return $q->whereMonth('user_request_payments.created_at', Carbon::now()->month);
                    case 'year':
                        return $q->whereYear('user_request_payments.created_at', Carbon::now()->year);
                }
            })
            ->selectRaw('
                providers.subscription_level,
                COUNT(*) as trip_count,
                SUM(user_request_payments.provider_commission) as total_commission,
                AVG(user_request_payments.provider_commission) as avg_commission
            ')
            ->groupBy('providers.subscription_level')
            ->get();
        
        // Configuration actuelle des pourcentages
        $currentConfig = [
            'tva' => Setting::get('dao_tva_percentage', 18),
            'insurance' => Setting::get('dao_insurance_percentage', 15),
            'syndicate' => Setting::get('dao_syndicate_percentage', 10),
            'cooperative' => Setting::get('dao_cooperative_percentage', 10),
        ];
        
        // Plans d'abonnement actifs
        $subscriptionPlans = SubscriptionPlan::where('status', 1)->get();
        
        // Statistiques des abonnements
        $subscriptionStats = Provider::selectRaw('
            subscription_level,
            COUNT(*) as count
        ')
        ->groupBy('subscription_level')
        ->get();
        
        return view('admin.dao_finance.dashboard', compact(
            'financialData',
            'commissionByLevel',
            'currentConfig',
            'subscriptionPlans',
            'subscriptionStats',
            'period'
        ));
    }
    
    /**
     * Mettre à jour les pourcentages de répartition
     */
    public function updateDistribution(Request $request)
    {
        $request->validate([
            'dao_tva_percentage' => 'required|numeric|min:0|max:100',
            'dao_insurance_percentage' => 'required|numeric|min:0|max:100',
            'dao_syndicate_percentage' => 'required|numeric|min:0|max:100',
            'dao_cooperative_percentage' => 'required|numeric|min:0|max:100',
        ]);
        
        // Vérifier que la somme ne dépasse pas 100%
        $total = $request->dao_tva_percentage + $request->dao_insurance_percentage + 
                 $request->dao_syndicate_percentage + $request->dao_cooperative_percentage;
        
        if ($total > 100) {
            return redirect()->back()->with('flash_error', 'La somme des pourcentages ne peut pas dépasser 100%');
        }
        
        Setting::set('dao_tva_percentage', $request->dao_tva_percentage);
        Setting::set('dao_insurance_percentage', $request->dao_insurance_percentage);
        Setting::set('dao_syndicate_percentage', $request->dao_syndicate_percentage);
        Setting::set('dao_cooperative_percentage', $request->dao_cooperative_percentage);
        Setting::save();
        
        return redirect()->back()->with('flash_success', 'Configuration de répartition mise à jour avec succès');
    }
    
    /**
     * Mettre à jour la configuration d'un niveau de commission
     */
    public function updateCommissionLevel(Request $request, $level)
    {
        $request->validate([
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
        ]);
        
        Setting::set("dao_commission_{$level}_type", $request->type);
        Setting::set("dao_commission_{$level}_value", $request->value);
        Setting::save();
        
        return redirect()->back()->with('flash_success', "Commission pour le niveau {$level} mise à jour");
    }
}
