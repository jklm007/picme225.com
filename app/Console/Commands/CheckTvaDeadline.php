<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Carbon\Carbon;
use Mail;
use Log;

class CheckTvaDeadline extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tva:check-deadline';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Vérifier et alerter sur les échéances TVA à venir';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🔍 Vérification des échéances TVA...');
        
        $now = Carbon::now();
        
        // Prochaine échéance : 15 du mois suivant
        $deadline = Carbon::create($now->year, $now->month, 15)->addMonth();
        
        if ($now->day >= 15) {
            $deadline->addMonth();
        }
        
        $daysRemaining = $now->diffInDays($deadline, false);
        
        $this->info("📅 Prochaine échéance : {$deadline->format('d/m/Y')} ({$daysRemaining} jours)");
        
        // Alertes selon les jours restants
        $shouldAlert = false;
        $urgency = 'info';
        
        if ($daysRemaining <= 3) {
            $shouldAlert = true;
            $urgency = 'critical';
            $this->error('🚨 URGENT : Échéance dans 3 jours ou moins !');
        } elseif ($daysRemaining <= 7) {
            $shouldAlert = true;
            $urgency = 'warning';
            $this->warn('⚠️ ATTENTION : Échéance dans 7 jours');
        } elseif ($daysRemaining <= 14) {
            $shouldAlert = true;
            $urgency = 'notice';
            $this->info('ℹ️ INFO : Échéance dans 14 jours');
        }
        
        if ($shouldAlert) {
            $admins = User::where('user_type', 'ADMIN')->get();
            
            foreach ($admins as $admin) {
                try {
                    Mail::send('emails.tva_deadline_alert', [
                        'admin' => $admin,
                        'deadline' => $deadline,
                        'days_remaining' => $daysRemaining,
                        'urgency' => $urgency,
                        'period' => $deadline->copy()->subMonth()->format('F Y')
                    ], function ($message) use ($admin, $urgency, $daysRemaining) {
                        $prefix = $urgency === 'critical' ? '🚨 URGENT' : '⚠️';
                        $message->to($admin->email)
                                ->subject("{$prefix} Échéance TVA dans {$daysRemaining} jours");
                    });
                    
                    $this->info("✅ Alerte envoyée à {$admin->email}");
                    
                } catch (\Exception $e) {
                    $this->error("❌ Erreur envoi à {$admin->email}: " . $e->getMessage());
                    Log::error('Erreur envoi alerte TVA', [
                        'admin' => $admin->email,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        } else {
            $this->info('✅ Pas d\'alerte nécessaire pour le moment');
        }
        
        return 0;
    }
}
