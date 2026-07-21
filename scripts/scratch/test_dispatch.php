<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Provider;
use App\ProviderService;
use Illuminate\Support\Facades\DB;

// Simuler la requête de dispatch exactement comme le backend le fait
// Position client : 5.345317, -4.024429 (Cocody) - meme position que le driver
$latitude  = 5.345317;
$longitude = -4.024429;
$distance  = 10; // rayon de 10km
$service_type_id = 1; // Taxi Vtc

echo "=== TEST DISPATCH SIMULATION ===\n";
echo "Client position: $latitude, $longitude\n";
echo "Search radius: {$distance}km\n";
echo "Service type: $service_type_id\n\n";

$Providers = Provider::with('service')
    ->select(DB::Raw("(6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) AS distance"), 'id', 'eco_wallet_balance', 'service_type_id', 'commune', 'first_name', 'last_name', 'status', 'latitude', 'longitude')
    ->where('status', 'approved')
    ->whereRaw("(6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance")
    ->whereHas('service', function ($query) use ($service_type_id) {
        $query->where('status', 'active');
        $query->where('service_type_id', $service_type_id);
    })
    ->orderBy('distance', 'asc')
    ->take(10)
    ->get();

echo "Drivers found: " . $Providers->count() . "\n\n";
foreach ($Providers as $p) {
    echo "  -> {$p->first_name} {$p->last_name} | ID={$p->id} | status={$p->status} | commune={$p->commune} | dist=" . round($p->distance, 2) . "km | eco_wallet={$p->eco_wallet_balance}\n";
    $canAfford = $p->canAffordCommission(5000, 15);
    echo "     canAffordCommission: " . ($canAfford ? "YES" : "NO") . "\n";
}

if ($Providers->count() == 0) {
    echo "WARNING: No drivers found! Check driver position, service assignment, or status.\n";
    
    // Debug: show all approved drivers
    $all = Provider::where('status', 'approved')->get();
    echo "\nAll approved drivers:\n";
    foreach ($all as $p) {
        echo "  ID={$p->id} {$p->first_name} lat={$p->latitude} lng={$p->longitude} commune={$p->commune}\n";
    }
}
