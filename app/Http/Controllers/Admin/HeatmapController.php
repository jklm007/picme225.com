<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HeatmapController extends Controller
{
    /**
     * Get the current precalculated heatmap (Predicted Demand Score)
     * GET /admin/heatmap/current
     */
    public function current(Request $request)
    {
        $hour = now()->hour;
        $day = now()->dayOfWeek; // 1 (Sunday) to 7 (Saturday) or depending on DB configuration
        // In MySQL DAYOFWEEK: 1=Sunday, 2=Monday... 7=Saturday
        // Laravel Carbon dayOfWeek: 0=Sunday, 1=Monday... 6=Saturday
        // To match MySQL DAYOFWEEK which is used in DemandPredictionService:
        $mysqlDayOfWeek = now()->dayOfWeek + 1;

        // Cache the active zones list for 1 hour to avoid heavy DB queries
        $zones = Cache::remember('active_heatmap_zones', 3600, function () {
            return DB::table('user_requests')
                ->where('created_at', '>=', now()->subDays(30))
                ->whereNotNull('s_geohash')
                ->selectRaw('SUBSTRING(s_geohash, 1, 4) as geohash')
                ->distinct()
                ->pluck('geohash')
                ->toArray();
        });

        $heatmap = [];
        foreach ($zones as $zone) {
            $cacheKey = "demand_{$zone}_{$hour}_{$mysqlDayOfWeek}";
            $pds = Cache::get($cacheKey, 0);

            if ($pds > 0) {
                $heatmap[] = [
                    'geohash' => $zone,
                    'pds'     => $pds
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data'    => $heatmap,
        ], 200);
    }
}
