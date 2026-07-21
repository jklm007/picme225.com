<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\UserRequests;

class ComputeRoutePolylineJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $requestId;
    public $s_lat;
    public $s_lng;
    public $d_lat;
    public $d_lng;
    public $waypoints;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($requestId, $s_lat, $s_lng, $d_lat, $d_lng, $waypoints)
    {
        $this->requestId = $requestId;
        $this->s_lat = $s_lat;
        $this->s_lng = $s_lng;
        $this->d_lat = $d_lat;
        $this->d_lng = $d_lng;
        $this->waypoints = $waypoints;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $routing = get_osrm_routing($this->s_lat, $this->s_lng, $this->d_lat, $this->d_lng, $this->waypoints);
            $route_key = $routing ? ($routing['geometry'] ?? '') : '';

            if ($route_key) {
                UserRequests::where('id', $this->requestId)->update(['route_key' => $route_key]);
            }
        } catch (\Exception $e) {
            Log::error("Error in ComputeRoutePolylineJob: " . $e->getMessage());
        }
    }
}
