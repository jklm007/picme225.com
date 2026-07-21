<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserRequestPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Setting;

class TvaAccountingController extends Controller
{
    /**
     * Dashboard de comptabilité TVA avec prévisions
     */
    public function dashboard(Request $request)
    {
        $year = $request->get('year', Carbon::now()->year);
        $month = $request->get('month', Carbon::now()->month);
        
        // Données TVA du mois sélectionné
        $currentMonthData = $this->getMonthlyTvaData($year, $month);
        
        // Historique des 12 derniers mois
        $historicalData = $this->getHistoricalTvaData(12);
        
        // Prévisions pour les 3 prochains mois
        $forecasts = $this->generateTvaForecasts(3);
        
        // Données annuelles
        $yearlyData = $this->getYearlyTvaData($year);
        
        // Prochaine échéance de déclaration
        $nextDeadline = $this->getNextTvaDeadline();
        
        // Taux de TVA actuel
        $tvaRate = Setting::get('dao_tva_percentage', 18);
        
        return view('admin.tva_accounting.dashboard', compact(
            'currentMonthData',
            'historicalData',
            'forecasts',
            'yearlyData',
            'nextDeadline',
            'tvaRate',
            'year',
            'month'
        ));
    }
    
    /**
     * Obtenir les données TVA d'un mois spécifique
     */
    private function getMonthlyTvaData($year, $month)
    {
        $data = UserRequestPayment::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->selectRaw('
                COUNT(*) as total_transactions,
                SUM(total) as total_revenue,
                SUM(provider_commission) as total_commission,
                SUM(tva_fee) as tva_collected,
                SUM(CASE WHEN payment_mode = "CARD" THEN tva_fee ELSE 0 END) as tva_paid_online,
                SUM(CASE WHEN payment_mode = "CASH" THEN tva_fee ELSE 0 END) as tva_cash
            ')
            ->first();
        
        // Calcul du taux effectif de TVA
        $data->effective_rate = $data->total_commission > 0 
            ? ($data->tva_collected / $data->total_commission) * 100 
            : 0;
        
        return $data;
    }
    
    /**
     * Obtenir l'historique TVA des N derniers mois
     */
    private function getHistoricalTvaData($months = 12)
    {
        $data = [];
        
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthData = UserRequestPayment::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->selectRaw('
                    SUM(tva_fee) as tva_collected,
                    COUNT(*) as transaction_count
                ')
                ->first();
            
            $data[] = [
                'month' => $date->format('M Y'),
                'month_num' => $date->month,
                'year' => $date->year,
                'tva_collected' => $monthData->tva_collected ?? 0,
                'transaction_count' => $monthData->transaction_count ?? 0,
            ];
        }
        
        return $data;
    }
    
    /**
     * Générer des prévisions TVA basées sur les tendances
     */
    private function generateTvaForecasts($months = 3)
    {
        // Récupérer les 6 derniers mois pour calculer la tendance
        $historical = $this->getHistoricalTvaData(6);
        
        // Calcul de la moyenne mobile
        $avgTva = collect($historical)->avg('tva_collected');
        
        // Calcul de la tendance (croissance/décroissance)
        $recentAvg = collect($historical)->slice(-3)->avg('tva_collected');
        $olderAvg = collect($historical)->slice(0, 3)->avg('tva_collected');
        $growthRate = $olderAvg > 0 ? (($recentAvg - $olderAvg) / $olderAvg) : 0;
        
        $forecasts = [];
        $baseAmount = $recentAvg;
        
        for ($i = 1; $i <= $months; $i++) {
            $date = Carbon::now()->addMonths($i);
            
            // Prévision avec tendance de croissance
            $forecastAmount = $baseAmount * (1 + ($growthRate * $i));
            
            // Scénarios optimiste et pessimiste (±20%)
            $forecasts[] = [
                'month' => $date->format('M Y'),
                'month_num' => $date->month,
                'year' => $date->year,
                'forecast_base' => round($forecastAmount, 2),
                'forecast_optimistic' => round($forecastAmount * 1.2, 2),
                'forecast_pessimistic' => round($forecastAmount * 0.8, 2),
                'growth_rate' => round($growthRate * 100, 2),
            ];
        }
        
        return $forecasts;
    }
    
    /**
     * Obtenir les données TVA annuelles
     */
    private function getYearlyTvaData($year)
    {
        $data = UserRequestPayment::whereYear('created_at', $year)
            ->selectRaw('
                SUM(tva_fee) as total_tva,
                COUNT(*) as total_transactions,
                SUM(provider_commission) as total_commission
            ')
            ->first();
        
        // TVA par trimestre
        $quarters = [];
        for ($q = 1; $q <= 4; $q++) {
            $startMonth = ($q - 1) * 3 + 1;
            $endMonth = $q * 3;
            
            // PostgreSQL-compatible: use Carbon date range instead of MONTH()
            $startDate = \Carbon\Carbon::create($year, $startMonth, 1)->startOfMonth();
            $endDate   = \Carbon\Carbon::create($year, $endMonth, 1)->endOfMonth();
            $quarterData = UserRequestPayment::whereBetween('created_at', [$startDate, $endDate])
                ->sum('tva_fee');
            
            $quarters["Q{$q}"] = $quarterData;
        }
        
        $data->quarters = $quarters;
        
        return $data;
    }
    
    /**
     * Calculer la prochaine échéance de déclaration TVA
     */
    private function getNextTvaDeadline()
    {
        $now = Carbon::now();
        
        // En Côte d'Ivoire, déclaration mensuelle avant le 15 du mois suivant
        $deadline = Carbon::create($now->year, $now->month, 15)->addMonth();
        
        if ($now->day >= 15) {
            $deadline->addMonth();
        }
        
        return [
            'date' => $deadline->format('d/m/Y'),
            'days_remaining' => $now->diffInDays($deadline),
            'period' => $deadline->copy()->subMonth()->format('M Y'),
        ];
    }
    
    /**
     * Exporter un rapport TVA pour une période
     */
    public function exportReport(Request $request)
    {
        $year = $request->get('year', Carbon::now()->year);
        $month = $request->get('month', Carbon::now()->month);
        
        $data = $this->getMonthlyTvaData($year, $month);
        
        // Détail des transactions
        $transactions = UserRequestPayment::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->with('request.user', 'request.provider')
            ->get();
        
        return view('admin.tva_accounting.export', compact('data', 'transactions', 'year', 'month'));
    }
    
    /**
     * Générer un rapport PDF (Alternative sans DomPDF)
     */
    public function generatePdf(Request $request)
    {
        $year = $request->get('year', Carbon::now()->year);
        $month = $request->get('month', Carbon::now()->month);
        
        $data = $this->getMonthlyTvaData($year, $month);
        
        // Détail des transactions
        $transactions = UserRequestPayment::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->with('request.user', 'request.provider')
            ->get();
        
        // Générer HTML pour PDF
        $html = view('admin.tva_accounting.pdf_template', compact('data', 'transactions', 'year', 'month'))->render();
        
        // Utiliser wkhtmltopdf si disponible, sinon HTML simple
        $filename = "rapport_tva_{$year}_{$month}.pdf";
        
        // Pour l'instant, on retourne le HTML qui peut être imprimé en PDF
        // TODO: Intégrer wkhtmltopdf ou une autre solution
        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', "inline; filename={$filename}");
    }
}
