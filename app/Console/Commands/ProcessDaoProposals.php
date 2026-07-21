<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DaoProposal;
use App\Services\DaoGovernanceService;
use Log;

class ProcessDaoProposals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dao:process-proposals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Traite et exécute automatiquement les propositions DAO dont la période de vote est terminée';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        set_time_limit(0);
        $this->info('Début du traitement des propositions DAO...');

        // Récupérer les propositions actives dont la date de fin est passée
        $proposals = DaoProposal::where('status', 'ACTIVE')
            ->where('end_time', '<=', now())
            ->get();

        if ($proposals->isEmpty()) {
            $this->info('Aucune proposition expirée à traiter.');
            return;
        }

        $governanceService = new DaoGovernanceService();
        $processedCount = 0;

        foreach ($proposals as $proposal) {
            $this->line("Traitement de la proposition #{$proposal->id}: {$proposal->title}");
            
            try {
                $result = $governanceService->execute($proposal);
                
                if ($result['status'] === 'success') {
                    $this->info("  [SUCCÈS] {$result['message']}");
                } else if ($result['status'] === 'failed' || $result['status'] === 'rejected') {
                    $this->warn("  [FINIE] {$result['message']}");
                } else {
                    $this->error("  [ERREUR] {$result['message']}");
                }
                
                $processedCount++;
            } catch (\Exception $e) {
                $this->error("  [ERREUR FATALE] " . $e->getMessage());
                Log::error("Artisan DAO Error on proposal #{$proposal->id}: " . $e->getMessage());
            }
        }

        $this->info("Traitement terminé. {$processedCount} propositions traitées.");
    }
}
