<?php

use App\Models\PromocodeUsage;
use App\Models\ServiceType;
use App\Models\Hospital;

function currency($value = '')
{
    if ($value == "") {
        return "0.00" . Setting::get('currency');
    } else {
        return $value . Setting::get('currency');
    }
}

function distance($value = '')
{
    if ($value == "") {
        return "0" . Setting::get('distance', 'Km');
    } else {
        return $value . Setting::get('distance', 'Km');
    }
}

function img($img)
{
    if (empty($img) || strpos($img, 'lorempixel.com') !== false || strpos($img, 'placeimg.com') !== false) {
        return asset('main/avatar.jpg');
    } else if (strpos($img, 'http') === 0) {
        // Already an absolute URL (R2, external CDN, etc.) — return as-is
        return $img;
    } else if (strpos($img, 'data:') === 0) {
        // Base64 data URI — return as-is
        return $img;
    } else {
        // Use explicitly the s3 disk (R2)
        try {
            return \Illuminate\Support\Facades\Storage::disk('s3')->url($img);
        } catch (\Throwable $e) {
            return asset('main/avatar.jpg');
        }
    }
}


function image($img)
{
    if (empty($img) || strpos($img, 'lorempixel.com') !== false || strpos($img, 'placeimg.com') !== false) {
        return asset('main/avatar.jpg');
    } else if (strpos($img, 'http') === 0) {
        return $img;
    } else {
        try {
            return \Illuminate\Support\Facades\Storage::disk('s3')->url($img);
        } catch (\Throwable $e) {
            return asset('main/avatar.jpg');
        }
    }
}

function promo_used_count($promo_id)
{
    return PromocodeUsage::where('status', 'ADDED')->where('promocode_id', $promo_id)->count();
}

function curl($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $return = curl_exec($ch);
    curl_close($ch);
    return $return;
}

function get_all_service_types()
{
    return ServiceType::all();
}

function get_all_hospitals()
{
    return Hospital::orderBy('hospital_address', 'asc')->get();
}

/**
 * Antigravity: Fallback distance calculation using Haversine formula (in meters)
 */
function get_distance_fallback($lat1, $lon1, $lat2, $lon2, $waypoints = null)
{
    if (empty($lat1) || empty($lon1) || empty($lat2) || empty($lon2))
        return 0;

    $earth_radius = 6371000; // in meters
    $distance = 0;
    
    $points = [];
    $points[] = ['lat' => $lat1, 'lon' => $lon1];
    
    if ($waypoints != null && !empty($waypoints)) {
        if (is_string($waypoints)) {
            $waypoints = json_decode($waypoints, true);
        }
        if (is_array($waypoints)) {
            foreach ($waypoints as $wp) {
                if (isset($wp['latitude']) && isset($wp['longitude'])) {
                    $points[] = ['lat' => $wp['latitude'], 'lon' => $wp['longitude']];
                }
            }
        }
    }
    
    $points[] = ['lat' => $lat2, 'lon' => $lon2];
    
    for ($i = 0; $i < count($points) - 1; $i++) {
        $p1 = $points[$i];
        $p2 = $points[$i+1];
        
        $dLat = deg2rad($p2['lat'] - $p1['lat']);
        $dLon = deg2rad($p2['lon'] - $p1['lon']);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($p1['lat'])) * cos(deg2rad($p2['lat'])) *
            sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        $distance += $earth_radius * $c;
    }

    // Add 20% to account for road curves instead of straight line
    return $distance * 1.2;
}

/**
 * Antigravity: Fallback duration estimate (in seconds)
 * Based on an average city speed of 30 km/h (8.33 m/s)
 */
function get_duration_estimate($distance_meters)
{
    $average_speed = 8.33; // m/s
    return round($distance_meters / $average_speed);
}

/**
 * Antigravity: Get distance, duration and geometry from OSRM
 */
function get_osrm_routing($s_lat, $s_lng, $d_lat, $d_lng, $waypoints = null)
{
    try {
        if (empty($s_lat) || empty($s_lng) || empty($d_lat) || empty($d_lng)) {
            return null;
        }

        // Arrondir à 4 décimales (~11 mètres) pour maximiser le taux de hit du cache
        $roundedSLat = round($s_lat, 4);
        $roundedSLng = round($s_lng, 4);
        $roundedDLat = round($d_lat, 4);
        $roundedDLng = round($d_lng, 4);

        $wpHash = '';
        if ($waypoints != null && !empty($waypoints)) {
            $wpHash = '_' . md5(is_array($waypoints) ? json_encode($waypoints) : (string)$waypoints);
        }

        $cacheKey = "osrm_route:{$roundedSLat}_{$roundedSLng}:{$roundedDLat}_{$roundedDLng}{$wpHash}";

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addHours(24), function() use ($s_lat, $s_lng, $d_lat, $d_lng, $waypoints) {
            $coordinates = "{$s_lng},{$s_lat}";
            
            if ($waypoints != null && !empty($waypoints)) {
                if (is_string($waypoints)) {
                    $waypoints = json_decode($waypoints, true);
                }
                if (is_array($waypoints)) {
                    foreach ($waypoints as $wp) {
                        if (isset($wp['longitude']) && isset($wp['latitude'])) {
                            $coordinates .= ";" . $wp['longitude'] . "," . $wp['latitude'];
                        }
                    }
                }
            }
            
            $coordinates .= ";{$d_lng},{$d_lat}";

            $url = "https://router.project-osrm.org/route/v1/driving/{$coordinates}?overview=full&steps=false";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2); // Connection timeout of 2 seconds
            curl_setopt($ch, CURLOPT_TIMEOUT, 3); // Timeout of 3 seconds instead of 10
            curl_setopt($ch, CURLOPT_USERAGENT, 'PickMePro/1.0');
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200)
                return null;

            $data = json_decode($response, true);
            if (!isset($data['routes'][0]))
                return null;

            return [
                'distance' => $data['routes'][0]['distance'], // meters
                'duration' => $data['routes'][0]['duration'], // seconds
                'geometry' => $data['routes'][0]['geometry'], // Encoded polyline
            ];
        });
    } catch (\Exception $e) {
        return null;
    }
}

function demo_mode()
{
    if (\Setting::get('demo_mode', 0) == 1) {
        return back()->with('flash_error', 'Disabled for demo purposes! Please contact us at info@appdupe.com');
    }
}

function get_static_map($s_lat, $s_lng, $d_lat, $d_lng, $route_key = null)
{
    $mapboxToken = config('services.mapbox.key') ?: env('MAPBOX_API_KEY');
    if (!empty($mapboxToken)) {
        $overlays = [];
        if (!empty($s_lng) && !empty($s_lat)) {
            $overlays[] = "pin-m-a+f44336({$s_lng},{$s_lat})";
        }
        if (!empty($d_lng) && !empty($d_lat)) {
            $overlays[] = "pin-m-b+4caf50({$d_lng},{$d_lat})";
        }
        if (!empty($route_key)) {
            // URL encode the encoded polyline string to handle special characters.
            $overlays[] = "path-5+191919-0.8(" . rawurlencode($route_key) . ")";
        }
        
        $overlayStr = implode(",", $overlays);
        return "https://api.mapbox.com/styles/v1/mapbox/streets-v11/static/{$overlayStr}/auto/320x130?access_token={$mapboxToken}";
    }

    // Fallback to Yandex static map API if Mapbox key is not found
    $overlays = [];
    $pt = [];
    if (!empty($s_lng) && !empty($s_lat)) {
        $pt[] = "{$s_lng},{$s_lat},pm2rdm";
    }
    if (!empty($d_lng) && !empty($d_lat)) {
        $pt[] = "{$d_lng},{$d_lat},pm2gnm";
    }
    
    $url = "https://static-maps.yandex.ru/1.x/?l=map&lang=fr_FR&size=320,130";
    if (!empty($pt)) {
        $url .= "&pt=" . implode("~", $pt);
    }
    if (!empty($s_lng) && !empty($s_lat) && !empty($d_lng) && !empty($d_lat)) {
        $url .= "&pl=c:191919ff,w:4,{$s_lng},{$s_lat},{$d_lng},{$d_lat}";
    }
    return $url;
}

function hasFeature($user, string $feature): bool
{
    if (!$user) {
        return false;
    }

    $pro_enabled = \Setting::get('pro_enabled', '1') === '1';

    // Les administrateurs ou les gestionnaires de flotte ont tous les accès par défaut
    if (isset($user->user_type) && in_array($user->user_type, ['FLEET', 'ADMIN'])) {
        return true;
    }

    // Détermination du niveau
    $userLevel = 0; // 0 = FREE / none

    if ($user instanceof \App\User || $user instanceof \App\Models\User || isset($user->member_tier)) {
        // Passager
        $tier = strtoupper($user->member_tier ?? 'FREE');
        if ($tier === 'STANDARD') {
            $userLevel = 1;
        } elseif ($tier === 'PREMIUM') {
            $userLevel = 2;
        } elseif ($tier === 'PRO') {
            $userLevel = 3;
        }
    } elseif ($user instanceof \App\Provider || $user instanceof \App\Models\Provider || isset($user->subscription_level)) {
        // Chauffeur
        $level = strtolower($user->subscription_level ?? 'none');
        if (in_array($level, ['standard', 'eco'])) {
            $userLevel = 1;
        } elseif ($level === 'pro') {
            $userLevel = 2;
        } elseif ($level === 'gold') {
            $userLevel = 3;
        }
    }

    // Si PRO n'est pas activé globalement dans les réglages
    if (!$pro_enabled) {
        return true;
    }

    // Association des fonctionnalités avec un niveau minimum (0: FREE, 1: STANDARD, 2: PREMIUM, 3: PRO)
    $featureRequirements = [
        'ride_booking'            => 0, // FREE
        'priority_dispatch'       => 2, // PREMIUM
        'advanced_statistics'     => 2, // PREMIUM
        'vehicle_without_driver'  => 3, // PRO
        'vip_support'             => 3, // PRO
        'partner_offers'          => 3, // PRO
    ];

    $requiredLevel = $featureRequirements[$feature] ?? 0;

    return $userLevel >= $requiredLevel;
}