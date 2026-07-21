<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Provider;
use App\ServiceType;
use App\Helpers\Helper;
use DB;

// Coordinates from logs
$latitude = 5.3826664;
$longitude = -3.9634956;
$d_latitude = 5.3336278;
$d_longitude = -4.0002364;
$d_address = "Be One House- Makeup, Abidjan, Abidjan, CÔte d’Ivoire";
$service_type_id = 1;
$distance = 100; // From settings

echo "Target Lat/Lng: $latitude, $longitude\n";

$ProvidersQuery = Provider::with(['service', 'subscriptionPlan', 'selectedServices'])
    ->select(DB::Raw("(6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) AS distance"), 
             'id', 'first_name', 'status', 'eco_wallet_balance', 'service_type_id', 'subscription_plan_id', 'is_smart_mode', 'smart_mode_type', 'smart_dest_lat', 'smart_dest_lng', 'smart_zone_radius', 'smart_communes', 'last_priority_action_at', 'priority')
    ->where('status', 'approved')
    ->whereRaw("(6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance");

$ProvidersQuery->whereHas('service', function ($query) use ($service_type_id) {
    $query->where('status', 'active');
    $query->where('service_type_id', $service_type_id);
});

// Category Filter
$requestedServiceCategoryId = 1; // Default for Sedan
$ProvidersQuery->where(function ($query) use ($requestedServiceCategoryId) {
    $query->whereHas('selectedServices', function ($q) use ($requestedServiceCategoryId) {
        $q->where('service_id', $requestedServiceCategoryId)
            ->where('is_active', true);
    })->orWhereDoesntHave('selectedServices');
});

$Providers = $ProvidersQuery->orderBy('distance', 'asc')->get();
echo "Initial Match Count: " . count($Providers) . "\n";

// solvability
$st = ServiceType::find($service_type_id);
$estimatedPrice = 122.3; // From logs
$commissionPercentage = $st->commission_percentage ?? 25;

$Providers = $Providers->filter(function ($provider) use ($estimatedPrice) {
    echo "  Checking Solvability ID:{$provider->id}...";
    $res = $provider->canAffordCommission($estimatedPrice);
    echo ($res ? 'Yes' : 'No') . "\n";
    return $res;
});

// smart mode
$request_d_lat = $d_latitude;
$request_d_lng = $d_longitude;
$request_s_lat = $latitude;
$request_s_lng = $longitude;
$request_d_address = $d_address;

$Providers = $Providers->filter(function ($provider) use ($request_s_lat, $request_s_lng, $request_d_lat, $request_d_lng, $request_d_address) {
    echo "  Checking Smart Mode ID:{$provider->id} (Mode: " . ($provider->is_smart_mode ? 'Y' : 'N') . ")...";
    if (!$provider->is_smart_mode) {
        echo "Passed (OFF)\n";
        return true;
    }

    if ($provider->smart_mode_type == 'HOME') {
        if (!$provider->smart_dest_lat || !$provider->smart_dest_lng) {
            echo "Passed (HOME but NO Target)\n";
            return true;
        }
        $dist_p_t = Helper::haversineGreatCircleDistance($request_s_lat, $request_s_lng, $provider->smart_dest_lat, $provider->smart_dest_lng);
        $dist_d_t = Helper::haversineGreatCircleDistance($request_d_lat, $request_d_lng, $provider->smart_dest_lat, $provider->smart_dest_lng);
        echo "Distances: P-T: $dist_p_t, D-T: $dist_d_t. Result: " . ($dist_d_t < ($dist_p_t - 100) ? 'Passed' : 'Failed') . "\n";
        return $dist_d_t < ($dist_p_t - 100);
    }
    echo "Passed (Other mode - skip for now)\n";
    return true;
});

echo "\nFinal Match Count: " . count($Providers) . "\n";
foreach ($Providers as $p) {
    echo "ID: {$p->id} | Name: {$p->first_name} | Distance: {$p->distance} km\n";
}
