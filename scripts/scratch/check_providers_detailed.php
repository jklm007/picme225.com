<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Provider;
use App\ProviderService;
use App\ServiceType;
use App\ProviderSelectedService;

$serviceTypes = ServiceType::all();
echo "=== SERVICE TYPES ===\n";
foreach ($serviceTypes as $st) {
    echo "ID: {$st->id} | Name: {$st->name} | Communal: " . ($st->is_communal ? 'Yes' : 'No') . " | Ambulance: " . ($st->ambulance ? 'Yes' : 'No') . "\n";
}

$providers = Provider::all();
echo "\n=== ALL PROVIDERS ===\n";
foreach ($providers as $p) {
    echo "ID: {$p->id} | Name: {$p->first_name} | Status: {$p->status} | Wallet: {$p->eco_wallet_balance} | Lat: {$p->latitude} | Lng: {$p->longitude} | Commune: {$p->commune}\n";
    
    $selected = ProviderSelectedService::where('provider_id', $p->id)->get();
    if ($selected->count() > 0) {
        echo "   Selected Services: ";
        foreach ($selected as $s) {
            echo "ID:{$s->service_id} (Active:" . ($s->is_active ? 'Y' : 'N') . ") ";
        }
        echo "\n";
    } else {
        echo "   No selected services (All active by default)\n";
    }
}
