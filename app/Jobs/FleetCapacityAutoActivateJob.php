<?php

namespace App\Jobs;

use App\Models\Service;
use App\Services\FeatureFlagService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FleetCapacityAutoActivateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     * Iterates over all Services and triggers auto-activation logic
     * through FeatureFlagService with a minimum-capacity threshold of 3.
     */
    public function handle(): void
    {
        $services = Service::all();

        $processed = 0;
        $activated = 0;
        $failed    = 0;

        /** @var FeatureFlagService $featureFlagService */
        $featureFlagService = app(FeatureFlagService::class);

        foreach ($services as $service) {
            try {
                $result = $featureFlagService->autoActivate($service->id, 3);

                if ($result) {
                    $activated++;
                    Log::info('[FleetCapacityAutoActivateJob] Service auto-activated', [
                        'service_id'   => $service->id,
                        'service_name' => $service->name ?? 'N/A',
                    ]);
                }

                $processed++;
            } catch (\Throwable $e) {
                $failed++;
                Log::error('[FleetCapacityAutoActivateJob] Failed to auto-activate service', [
                    'service_id'   => $service->id,
                    'service_name' => $service->name ?? 'N/A',
                    'error'        => $e->getMessage(),
                    'trace'        => $e->getTraceAsString(),
                ]);
            }
        }

        Log::info('[FleetCapacityAutoActivateJob] Fleet capacity auto-activation completed', [
            'total_services' => $services->count(),
            'processed'      => $processed,
            'activated'      => $activated,
            'failed'         => $failed,
        ]);
    }
}
