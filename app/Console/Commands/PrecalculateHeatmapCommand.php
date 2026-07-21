<?php

namespace App\Console\Commands;

use App\Services\DemandPredictionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Artisan Command : picme:precalculate-heatmap
 *
 * Pré-calcule la heatmap de prédiction de demande et remplit le cache Redis.
 * À planifier dans Kernel.php : ->dailyAt('02:00')
 *
 * Usage: php artisan picme:precalculate-heatmap
 */
class PrecalculateHeatmapCommand extends Command
{
    protected $signature   = 'picme:precalculate-heatmap';
    protected $description = 'Pré-calcule la heatmap de demande V2.3 et remplit le cache Redis.';

    public function handle(DemandPredictionService $service): int
    {
        $this->info('[PicMe V2.3] Précalcul de la heatmap en cours...');
        $start = microtime(true);

        $count = $service->precalculateHeatmap();

        $elapsed = round((microtime(true) - $start), 2);
        $this->info("[PicMe V2.3] ✅ {$count} entrées précalculées en {$elapsed}s");

        Log::info("[PrecalculateHeatmap] Terminé : {$count} entrées en {$elapsed}s");
        return Command::SUCCESS;
    }
}
