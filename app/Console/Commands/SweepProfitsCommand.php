<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\GatewayNode;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class SweepProfitsCommand extends Command
{
    protected $signature = 'profits:sweep';
    protected $description = 'Vérifie les bénéfices par réseau et génère un Payout vers le numéro de charges.';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info("Début du balayage des bénéfices (Profit Sweep) par réseau...");
        
        // On récupère TOUS les noeuds de type PROFIT (un par réseau : WAVE, ORANGE, etc.)
        $profitNodes = GatewayNode::where('type', 'PROFIT')->get();
        
        if ($profitNodes->isEmpty()) {
            $this->error("Aucun Node PROFIT introuvable dans la base de données.");
            return 1;
        }

        foreach ($profitNodes as $profitNode) {
            $network = strtoupper($profitNode->network);
            $balance = $profitNode->current_balance;
            $this->info("--- Réseau {$network} ---");
            $this->info("Solde virtuel : {$balance} FCFA");

            if ($balance >= 10000) {
                $this->info("Seuil de 10 000 FCFA atteint sur {$network}.");

                // Récupérer le numéro cible spécifique au réseau
                $settingKey = 'profit_phone_' . strtolower($network);
                $profitPhone = Setting::where('key', $settingKey)->value('value') ?? '0000000000';
                
                if ($profitPhone == '0000000000') {
                    $this->error("Aucun numéro de profit configuré pour {$network} ({$settingKey}). Payout annulé.");
                    continue;
                }

                Log::channel('gateway')->info("PROFIT SWEEP: Ordre de transfert de {$balance} FCFA vers {$profitPhone} via {$network}.");
                
                \App\Models\Transaction::create([
                    'user_id' => 1,
                    'type' => 'PROFIT_SWEEP',
                    'amount' => -$balance,
                    'status' => 'PENDING',
                    'payment_method' => strtolower($network), // Forcer le réseau correspondant !
                    'gateway_node_id' => $profitNode->id,
                    'target_phone' => $profitPhone,
                    'description' => "Balayage des bénéfices {$network}."
                ]);
                
                $this->info("Ordre de transfert (Payout) généré pour {$balance} FCFA sur le réseau {$network}.");
            } else {
                $this->info("Le seuil n'est pas encore atteint sur {$network}.");
            }
        }

        return 0;
    }
}
