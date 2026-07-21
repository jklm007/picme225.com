<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Provider;
use App\ProviderService;
use Illuminate\Support\Facades\DB;

// Vérifier le driver de test (ID=15)
$driver = Provider::find(15);
if (!$driver) {
    echo "Driver ID=15 NOT FOUND!\n";
    exit;
}
echo "=== TEST DRIVER (ID=15) ===\n";
echo "Name: {$driver->first_name} {$driver->last_name}\n";
echo "Status: {$driver->status}\n";
echo "Commune: {$driver->commune}\n";
echo "Position: lat={$driver->latitude}, lng={$driver->longitude}\n";
echo "Eco Wallet: {$driver->eco_wallet_balance}\n\n";

$services = ProviderService::where('provider_id', 15)->get();
echo "Assigned Services:\n";
foreach ($services as $ps) {
    echo "  service_type_id={$ps->service_type_id} status={$ps->status}\n";
}

// Simuler dispatch pour ce driver (service 1 = Taxi Vtc)
echo "\n=== DISPATCH TEST (Taxi Vtc, service_type=1) ===\n";
$latitude = 5.345317; $longitude = -4.024429;
$Providers = Provider::with('service')
    ->select(DB::Raw("(6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) AS distance"), 'id', 'eco_wallet_balance', 'status', 'first_name', 'last_name')
    ->where('status', 'approved')
    ->whereRaw("(6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= 10")
    ->whereHas('service', fn($q) => $q->where('status', 'active')->where('service_type_id', 1))
    ->orderBy('distance', 'asc')
    ->get();

echo "Drivers in radius: " . $Providers->count() . "\n";
foreach ($Providers as $p) {
    $afford = $p->canAffordCommission(5000, 15);
    echo "  -> {$p->first_name} (ID={$p->id}) dist=" . round($p->distance, 2) . "km wallet={$p->eco_wallet_balance} canAfford=" . ($afford?"YES":"NO") . "\n";
}
