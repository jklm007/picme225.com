<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Provider;
use App\Services\DispatchEngine\ScoreService;
use App\Services\DispatchEngine\GeoService;
use Illuminate\Support\Facades\Log;

class DispatchEngineUpdateScores extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dispatch:update-scores';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Met à jour le score à long terme (dispatch_score) de tous les chauffeurs approuvés pour le moteur IA.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Démarrage de la mise à jour des scores IA...');
        Log::info('[DispatchEngine] Début du cron: dispatch:update-scores');

        $scoreService = new ScoreService(new GeoService());
        
        // On traite par lots pour ne pas saturer la RAM (Chunk de 100)
        Provider::where('status', 'approved')->chunk(100, function ($providers) use ($scoreService) {
            foreach ($providers as $provider) {
                $scoreService->updateLongTermScore($provider);
            }
        });

        $this->info('Mise à jour terminée avec succès.');
        Log::info('[DispatchEngine] Fin du cron: dispatch:update-scores');
    }
}
