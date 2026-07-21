<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Admin\TvaAccountingController;
use App\Models\TaxExemptionConfig;
use App\Models\User;
use Carbon\Carbon;
use Mail;
use Log;

class SendMonthlyTvaReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tva:send-monthly-report {--test}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envoyer le rapport TVA mensuel automatique aux administrateurs';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $isTest = $this->option('test');
        
        $this->info('🚀 Génération du rapport TVA mensuel...');
        
        // Mois précédent
        $lastMonth = Carbon::now()->subMonth();
        $year = $lastMonth->year;
        $month = $lastMonth->month;
        
        // Récupérer les données via le controller
        $controller = new TvaAccountingController();
        $data = $controller->getMonthlyTvaData($year, $month);
        
        // Vérifier si exonération active
        $exemptionActive = TaxExemptionConfig::where('is_active', true)
            ->where('start_date', '<=', Carbon::now())
            ->where('end_date', '>=', Carbon::now())
            ->exists();
        
        // Liste des administrateurs
        $admins = User::where('user_type', 'ADMIN')->get();
        
        if ($admins->isEmpty()) {
            $this->error('❌ Aucun administrateur trouvé');
            return 1;
        }
        
        $this->info("📧 Envoi du rapport à {$admins->count()} administrateur(s)...");
        
        foreach ($admins as $admin) {
            try {
                Mail::send('emails.tva_monthly_report', [
                    'admin' => $admin,
                    'data' => $data,
                    'year' => $year,
                    'month' => $month,
                    'month_name' => $lastMonth->format('F Y'),
                    'exemption_active' => $exemptionActive
                ], function ($message) use ($admin, $lastMonth) {
                    $message->to($admin->email)
                            ->subject('Rapport TVA Mensuel - ' . $lastMonth->format('F Y'));
                });
                
                $this->info("✅ Rapport envoyé à {$admin->email}");
                
            } catch (\Exception $e) {
                $this->error("❌ Erreur envoi à {$admin->email}: " . $e->getMessage());
                Log::error('Erreur envoi rapport TVA', [
                    'admin' => $admin->email,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        $this->info('✅ Rapport TVA mensuel envoyé avec succès !');
        
        return 0;
    }
}
