<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HeatmapController extends Controller
{
    /**
     * Retourne les zones actives avec leur niveau de demande.
     * Utilisé par l'application User pour afficher la carte de chaleur.
     * GET /api/user/active-zones
     *
     * Réponse : tableau de zones avec geohash, lat, lng, radius, intensity (0-100)
     */
    public function activeZones(Request $request)
    {
        $hour          = now()->hour;
        $mysqlDayOfWeek = now()->dayOfWeek + 1; // 1=Sunday ... 7=Saturday

        // Cache la liste des zones avec activité récente (30 derniers jours)
        $zones = Cache::remember('active_heatmap_zones', 3600, function () {
            return DB::table('user_requests')
                ->where('created_at', '>=', now()->subDays(30))
                ->whereNotNull('s_geohash')
                ->selectRaw('SUBSTRING(s_geohash, 1, 4) as geohash')
                ->distinct()
                ->pluck('geohash')
                ->toArray();
        });

        $result = [];
        foreach ($zones as $geohash) {
            $cacheKey = "demand_{$geohash}_{$hour}_{$mysqlDayOfWeek}";
            $pds      = Cache::get($cacheKey, 0);

            if ($pds > 0) {
                // Décoder le geohash en lat/lng
                [$lat, $lng] = $this->decodeGeohash($geohash);

                // Normaliser le PDS en intensité 0-100
                $intensity = min(100, (int) $pds);

                $result[] = [
                    'geohash'   => $geohash,
                    'lat'       => $lat,
                    'lng'       => $lng,
                    'intensity' => $intensity,
                    // rayon en mètres (précision geohash 4 ≈ ±20km → on affiche 1500m)
                    'radius'    => 1500,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data'    => $result,
        ]);
    }

    /**
     * Decode a geohash string into [latitude, longitude].
     * Precision 4 gives ~±20km accuracy, sufficient for zone circles.
     */
    private function decodeGeohash(string $geohash): array
    {
        $chars = '0123456789bcdefghjkmnpqrstuvwxyz';
        $bits  = [16, 8, 4, 2, 1];

        $even    = true;
        $lat     = [-90.0, 90.0];
        $lng     = [-180.0, 180.0];

        foreach (str_split(strtolower($geohash)) as $char) {
            $cd = strpos($chars, $char);
            foreach ($bits as $bit) {
                if ($even) {
                    $mid = ($lng[0] + $lng[1]) / 2;
                    if ($cd & $bit) {
                        $lng[0] = $mid;
                    } else {
                        $lng[1] = $mid;
                    }
                } else {
                    $mid = ($lat[0] + $lat[1]) / 2;
                    if ($cd & $bit) {
                        $lat[0] = $mid;
                    } else {
                        $lat[1] = $mid;
                    }
                }
                $even = !$even;
            }
        }

        return [
            round(($lat[0] + $lat[1]) / 2, 6),
            round(($lng[0] + $lng[1]) / 2, 6),
        ];
    }
}
