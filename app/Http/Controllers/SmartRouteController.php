<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SmartRouteController extends Controller
{
    /**
     * Get optimized route using the Tri-Modal Architecture (Cache -> Mapbox -> Google -> OSRM)
     */
    public function get_smart_route(Request $request)
    {
        $request->validate([
            's_latitude' => 'required|numeric',
            's_longitude' => 'required|numeric',
            'd_latitude' => 'required|numeric',
            'd_longitude' => 'required|numeric',
        ]);

        Log::info("SmartRoute: New Request Received", $request->all());

        $s_lat = $request->s_latitude;
        $s_lng = $request->s_longitude;
        $d_lat = $request->d_latitude;
        $d_lng = $request->d_longitude;

        // Create a unique cache key for this route (rounded to 3 decimal places ~100m precision to maximize hits)
        $cacheKey = "route_" . round($s_lat, 3) . "_" . round($s_lng, 3) . "_" . round($d_lat, 3) . "_" . round($d_lng, 3);

        // LEVEL 1: CHECK CACHE (Valid for 20 minutes = 1200 seconds)
        if (Cache::has($cacheKey)) {
            Log::info("SmartRoute: Cache HIT for $cacheKey");
            $cachedData = Cache::get($cacheKey);
            $cachedData['cached'] = true;
            return response()->json($cachedData);
        }

        Log::info("SmartRoute: Cache MISS for $cacheKey. Calculating new optimal route.");

        $mapboxKey = config('services.mapbox.key') ?: env('MAPBOX_API_KEY', '');
        $googleKey = config('services.google.map_key') ?: env('GOOGLE_MAP_API_KEY_DIRECTIONS', '');

        $routeData = null;

        // Quota checks using Cache
        $currentMonth = date('Y_m');
        $mapboxCacheKey = "quota_mapbox_calls_" . $currentMonth;
        $googleCacheKey = "quota_google_calls_" . $currentMonth;

        $mapboxCount = (int) Cache::get($mapboxCacheKey, 0);
        $googleCount = (int) Cache::get($googleCacheKey, 0);

        $mapboxLimit = (int) env('MAPBOX_MAX_MONTHLY_LIMIT', 95000);
        $googleLimit = (int) env('GOOGLE_MAX_MONTHLY_LIMIT', 18000);

        // LEVEL 1: MAPBOX Directions Traffic API (Trafic réel, alternatives & péages)
        if (!empty($mapboxKey) && $mapboxKey != 'votre_cle_mapbox_ici') {
            if ($mapboxCount < $mapboxLimit) {
                try {
                    $url = "https://api.mapbox.com/directions/v5/mapbox/driving-traffic/{$s_lng},{$s_lat};{$d_lng},{$d_lat}?alternatives=true&steps=true&geometries=polyline&overview=full&annotations=distance,duration,speed&access_token={$mapboxKey}";
                    Log::info("SmartRoute: Requesting Mapbox - $url");
                    
                    $response = Http::timeout(6)->get($url);
                    $routes = [];

                    if ($response->successful()) {
                        $data = $response->json();
                        $routes = array_merge($routes, $data['routes'] ?? []);
                    } else {
                        Log::warning("SmartRoute: Mapbox HTTP error " . $response->status());
                    }

                    // Request toll-free alternatives
                    $urlNoToll = $url . "&exclude=toll";
                    Log::info("SmartRoute: Requesting Mapbox (No Toll) - $urlNoToll");
                    $responseNoToll = Http::timeout(6)->get($urlNoToll);

                    if ($responseNoToll->successful()) {
                        $dataNoToll = $responseNoToll->json();
                        // Append non-duplicate routes
                        foreach (($dataNoToll['routes'] ?? []) as $ntRoute) {
                            $isDuplicate = false;
                            foreach ($routes as $r) {
                                if (abs($r['distance'] - $ntRoute['distance']) < 50) {
                                    $isDuplicate = true;
                                    break;
                                }
                            }
                            if (!$isDuplicate) {
                                $routes[] = $ntRoute;
                            }
                        }
                    }

                    if (count($routes) > 0) {
                        $routeData = $this->formatMapboxResponse($routes);
                        $routeData['provider'] = 'mapbox';
                        Log::info("SmartRoute: Mapbox OK - " . count($routes) . " routes found.");

                        // If Mapbox only returned 1 route, supplement with OSRM detours
                        if (count($routeData['routes']) < 2) {
                            Log::info("SmartRoute: Mapbox returned only 1 route. Supplementing with OSRM detours.");
                            $detourRight = $this->generateOsrmDetourRoute($s_lat, $s_lng, $d_lat, $d_lng, 1);
                            $detourLeft  = $this->generateOsrmDetourRoute($s_lat, $s_lng, $d_lat, $d_lng, -1);
                            if ($detourRight) $routeData['routes'][] = $detourRight;
                            if ($detourLeft)  $routeData['routes'][] = $detourLeft;
                        }
                        
                        // Increment Mapbox quota counter (counts as 2 API calls)
                        Cache::put($mapboxCacheKey, $mapboxCount + 2, 1440 * 31);
                    }
                } catch (\Exception $e) {
                    Log::error("SmartRoute: Mapbox API Error - " . $e->getMessage());
                }
            } else {
                Log::warning("SmartRoute: Mapbox monthly quota reached ($mapboxCount/$mapboxLimit). Falling back to Google or OSRM.");
            }
        }

        // LEVEL 2: GOOGLE MAPS API (Alternatives, trafic en temps réel, péages)
        if (!$routeData && !empty($googleKey) && $googleKey != 'votre_cle_google_ici') {
            if ($googleCount < $googleLimit) {
                try {
                    $url = "https://maps.googleapis.com/maps/api/directions/json?origin={$s_lat},{$s_lng}&destination={$d_lat},{$d_lng}&alternatives=true&departure_time=now&key={$googleKey}";
                    Log::info("SmartRoute: Requesting Google Maps - $url");
                    
                    $response = Http::timeout(6)->get($url);

                    if ($response->successful()) {
                        $data = $response->json();
                        if ($data['status'] == 'OK' && isset($data['routes']) && count($data['routes']) > 0) {
                            $routeData = $this->formatGoogleResponse($data['routes']);
                            $routeData['provider'] = 'google';
                            Log::info("SmartRoute: Google Maps OK - " . count($data['routes']) . " routes found.");

                            // If Google only returned 1 route, supplement with OSRM detours
                            if (count($routeData['routes']) < 2) {
                                Log::info("SmartRoute: Google returned only 1 route. Supplementing with OSRM detours.");
                                $detourRight = $this->generateOsrmDetourRoute($s_lat, $s_lng, $d_lat, $d_lng, 1);
                                $detourLeft  = $this->generateOsrmDetourRoute($s_lat, $s_lng, $d_lat, $d_lng, -1);
                                if ($detourRight) $routeData['routes'][] = $detourRight;
                                if ($detourLeft)  $routeData['routes'][] = $detourLeft;
                            }
                            
                            // Increment Google quota counter
                            Cache::put($googleCacheKey, $googleCount + 1, 1440 * 31);
                        }
                    } else {
                        Log::warning("SmartRoute: Google Maps HTTP error " . $response->status());
                    }
                } catch (\Exception $e) {
                    Log::error("SmartRoute: Google Maps API Error - " . $e->getMessage());
                }
            } else {
                Log::warning("SmartRoute: Google Maps monthly quota reached ($googleCount/$googleLimit). Falling back to OSRM.");
            }
        }

        // LEVEL 3: OSRM (Alternatives gratuites, sans trafic/péage)
        if (!$routeData) {
            try {
                $url = "https://router.project-osrm.org/route/v1/driving/{$s_lng},{$s_lat};{$d_lng},{$d_lat}?overview=full&geometries=polyline&alternatives=3";
                Log::info("SmartRoute: Calling OSRM - $url");
                $response = Http::timeout(8)->get($url);

                if ($response->successful()) {
                    $data = $response->json();
                    $routes = $data['routes'] ?? [];
                    if (count($routes) > 0) {
                        $formatted = [];
                        foreach ($routes as $route) {
                            $formatted[] = [
                                'distance' => $route['distance'],
                                'duration' => $route['duration'],
                                'polyline' => $route['geometry'],
                                'has_toll' => false
                            ];
                        }

                        // Generate detour routes if OSRM returned fewer than 3 routes
                        if (count($routes) < 3) {
                            Log::info("SmartRoute: OSRM returned " . count($routes) . " route(s). Generating detour alternatives...");
                            // Right-side detour
                            $detourRight = $this->generateOsrmDetourRoute($s_lat, $s_lng, $d_lat, $d_lng, 1);
                            if ($detourRight) $formatted[] = $detourRight;
                            // Left-side detour (only if still < 3 routes)
                            if (count($formatted) < 3) {
                                $detourLeft = $this->generateOsrmDetourRoute($s_lat, $s_lng, $d_lat, $d_lng, -1);
                                if ($detourLeft) $formatted[] = $detourLeft;
                            }
                        }

                        $routeData = [
                            'routes' => $formatted,
                            'status' => 'success',
                            'provider' => 'osrm'
                        ];
                        Log::info("SmartRoute: OSRM OK - " . count($formatted) . " routes returned.");
                    } else {
                        Log::warning("SmartRoute: OSRM returned no routes. Code: " . ($data['code'] ?? 'N/A'));
                    }
                } else {
                    Log::warning("SmartRoute: OSRM HTTP error " . $response->status());
                }
            } catch (\Exception $e) {
                Log::error("SmartRoute: OSRM Exception - " . $e->getMessage());
            }
        }

        // LEVEL 4: LAST RESORT - Straight-line "à vol d'oiseau" fallback
        if (!$routeData) {
            Log::warning("SmartRoute: All routing engines failed. Falling back to straight-line geodesic route.");
            $straightRoute = $this->generateStraightLineRoute($s_lat, $s_lng, $d_lat, $d_lng);
            if ($straightRoute) {
                $routeData = [
                    'routes' => [$straightRoute],
                    'status' => 'success',
                    'provider' => 'straight_line'
                ];
            }
        }

        if ($routeData) {
            // Save to Cache for 20 minutes (1200 seconds)
            Cache::put($cacheKey, $routeData, 1200);
            $routeData['cached'] = false;
            return response()->json($routeData);
        }

        return response()->json(['error' => 'Unable to calculate routes using any provider.'], 500);
    }

    private function formatMapboxResponse($routes)
    {
        $formatted = [];
        foreach ($routes as $route) {
            $hasToll = false;
            
            // Mapbox Toll Detection via steps and intersections classes
            if (isset($route['legs'])) {
                foreach ($route['legs'] as $leg) {
                    if (isset($leg['steps'])) {
                        foreach ($leg['steps'] as $step) {
                            if (isset($step['intersections'])) {
                                foreach ($step['intersections'] as $intersection) {
                                    if (isset($intersection['classes']) && in_array('toll', $intersection['classes'])) {
                                        $hasToll = true;
                                        break 3; // Break out of legs, steps, and intersections loop only
                                    }
                                }
                            }
                        }
                    }
                }
            }
            
            $formatted[] = [
                'distance' => $route['distance'], // in meters
                'duration' => $route['duration'], // in seconds
                'polyline' => $route['geometry'], // Encoded polyline string
                'has_toll' => $hasToll
            ];
        }
        return ['routes' => $formatted, 'status' => 'success'];
    }

    private function formatGoogleResponse($routes)
    {
        $formatted = [];
        foreach ($routes as $route) {
            $leg = $route['legs'][0];
            $distance = $leg['distance']['value']; // meters
            
            // duration_in_traffic represents the live traffic duration
            $duration = isset($leg['duration_in_traffic']) ? $leg['duration_in_traffic']['value'] : $leg['duration']['value'];
            
            // Google Maps Toll Detection
            $hasToll = false;
            if (isset($route['warnings'])) {
                foreach ($route['warnings'] as $warning) {
                    if (stripos($warning, 'péage') !== false || stripos($warning, 'toll') !== false) {
                        $hasToll = true;
                    }
                }
            }

            $formatted[] = [
                'distance' => $distance,
                'duration' => $duration,
                'polyline' => $route['overview_polyline']['points'],
                'has_toll' => $hasToll
            ];
        }
        return ['routes' => $formatted, 'status' => 'success'];
    }

    private function formatOsrmResponse($routes)
    {
        $formatted = [];
        foreach ($routes as $route) {
            $formatted[] = [
                'distance' => $route['distance'],
                'duration' => $route['duration'],
                'polyline' => $route['geometry'],
                'has_toll' => false // Public OSRM does not provide reliable toll alerts
            ];
        }
        return ['routes' => $formatted, 'status' => 'success'];
    }

    /**
     * Generate an OSRM detour route via a waypoint offset perpendicular to the main route.
     * $direction: +1 = right side of route, -1 = left side of route
     * The offset is scaled by distance so short trips get small detours and long trips bigger ones.
     */
    private function generateOsrmDetourRoute($s_lat, $s_lng, $d_lat, $d_lng, $direction = 1)
    {
        $midLat = ($s_lat + $d_lat) / 2;
        $midLng = ($s_lng + $d_lng) / 2;
        
        $dLat = $d_lat - $s_lat;
        $dLng = $d_lng - $s_lng;
        $len = sqrt($dLat * $dLat + $dLng * $dLng);

        // Scale offset between 0.005° (~550m) and 0.02° (~2.2km) based on route length
        $offsetScale = min(0.02, max(0.005, $len * 0.25));
        
        if ($len > 0) {
            // Perpendicular vector (normalized), then scaled
            $offsetLat = $direction * (-($dLng / $len) * $offsetScale);
            $offsetLng = $direction * (($dLat / $len) * $offsetScale);
        } else {
            $offsetLat = $direction * 0.005;
            $offsetLng = $direction * 0.005;
        }
        
        $waypointLat = $midLat + $offsetLat;
        $waypointLng = $midLng + $offsetLng;
        
        try {
            $url = "https://router.project-osrm.org/route/v1/driving/{$s_lng},{$s_lat};{$waypointLng},{$waypointLat};{$d_lng},{$d_lat}?overview=full&geometries=polyline";
            $response = Http::timeout(6)->get($url);
            
            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['routes'][0])) {
                    $route = $data['routes'][0];
                    return [
                        'distance' => $route['distance'],
                        'duration' => $route['duration'],
                        'polyline' => $route['geometry'],
                        'has_toll' => false
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::warning("SmartRoute detour generation failed: " . $e->getMessage());
        }
        return null;
    }

    private function generateStraightLineRoute($s_lat, $s_lng, $d_lat, $d_lng)
    {
        $distance = $this->haversineDistance($s_lat, $s_lng, $d_lat, $d_lng);
        $duration = $distance / (40 / 3.6); // 40 km/h in m/s

        $polyline = $this->encodePolyline([
            [$s_lat, $s_lng],
            [$d_lat, $d_lng]
        ]);

        return [
            'distance'       => $distance,
            'duration'       => $duration,
            'polyline'       => $polyline,
            'has_toll'       => false,
            'is_straight_line' => true // Flag to show special message in app
        ];
    }

    private function haversineDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // in meters
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    private function encodeDifference($value)
    {
        $value = $value < 0 ? ~($value << 1) : $value << 1;
        $chunks = '';
        while ($value >= 0x20) {
            $chunks .= chr((0x20 | ($value & 0x1f)) + 63);
            $value >>= 5;
        }
        $chunks .= chr($value + 63);
        return $chunks;
    }

    private function encodePolyline($points)
    {
        $polyline = '';
        $lastLat = 0;
        $lastLng = 0;
        foreach ($points as $point) {
            $lat = round($point[0] * 1e5);
            $lng = round($point[1] * 1e5);
            $dLat = $lat - $lastLat;
            $dLng = $lng - $lastLng;
            $lastLat = $lat;
            $lastLng = $lng;
            $polyline .= $this->encodeDifference($dLat) . $this->encodeDifference($dLng);
        }
        return $polyline;
    }
}
