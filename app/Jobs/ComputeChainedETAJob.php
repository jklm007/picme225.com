<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Services\DispatchEngine\RoutingService;

class ComputeChainedETAJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $driverLat;
    public $driverLng;
    public $dropoffLat;
    public $dropoffLng;
    public $pickupLat;
    public $pickupLng;
    public $cacheKeyCurrent;
    public $cacheKeyNext;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($driverLat, $driverLng, $dropoffLat, $dropoffLng, $pickupLat, $pickupLng, $cacheKeyCurrent, $cacheKeyNext)
    {
        $this->driverLat = $driverLat;
        $this->driverLng = $driverLng;
        $this->dropoffLat = $dropoffLat;
        $this->dropoffLng = $dropoffLng;
        $this->pickupLat = $pickupLat;
        $this->pickupLng = $pickupLng;
        $this->cacheKeyCurrent = $cacheKeyCurrent;
        $this->cacheKeyNext = $cacheKeyNext;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $routing = new RoutingService();

            // Compute current trip ETA
            if (!Cache::has($this->cacheKeyCurrent)) {
                $routeCurrent = $routing->getRouteEstimate(
                    (float) $this->driverLat, (float) $this->driverLng,
                    (float) $this->dropoffLat, (float) $this->dropoffLng
                );
                // Cache for 60 seconds
                Cache::put($this->cacheKeyCurrent, $routeCurrent, 60);
            }

            // Compute next trip ETA
            if (!Cache::has($this->cacheKeyNext)) {
                $routeNext = $routing->getRouteEstimate(
                    (float) $this->dropoffLat, (float) $this->dropoffLng,
                    (float) $this->pickupLat, (float) $this->pickupLng
                );
                // Cache for 60 seconds
                Cache::put($this->cacheKeyNext, $routeNext, 60);
            }

        } catch (\Exception $e) {
            Log::error("Error in ComputeChainedETAJob: " . $e->getMessage());
        }
    }
}
