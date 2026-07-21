<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Provider;

$latitude  = 5.345317;
$longitude = -4.024429;
$distance  = 10;

echo "=== DISPATCH TEST (Taxi Vtc = ID 1, Woro-Woro = ID 15) ===\n";

foreach ([1, 15] as $service_type_id) {
    $Providers = Provider::with('service')
        ->select(DB::Raw("(6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) AS distance"),
            'id', 'eco_wallet_balance', 'status', 'first_name', 'last_name', 'commune')
        ->where('status', 'approved')
        ->whereRaw("(6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance")
        ->whereHas('service', fn($q) => $q->where('status', 'active')->where('service_type_id', $service_type_id))
        ->orderBy('distance', 'asc')
        ->take(10)->get();

    $Providers = $Providers->filter(fn($p) => $p->canAffordCommission(5000, 15));

    echo "\nService ID=$service_type_id : " . $Providers->count() . " driver(s) found\n";
    foreach ($Providers as $p) {
        echo "  -> {$p->first_name} {$p->last_name} (ID={$p->id}) | dist=" . round($p->distance, 2) . "km | wallet={$p->eco_wallet_balance} | commune={$p->commune}\n";
    }
}
echo "\n=== Redis Test ===\n";
echo \Illuminate\Support\Facades\Redis::ping() . "\n";
