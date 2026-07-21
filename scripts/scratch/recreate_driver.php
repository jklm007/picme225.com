<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Provider;
use App\ProviderService;

// Lister tous les providers
$all = Provider::all(['id', 'first_name', 'last_name', 'email', 'status', 'latitude', 'longitude', 'commune', 'eco_wallet_balance']);
echo "ALL PROVIDERS:\n";
foreach ($all as $p) {
    echo "  ID={$p->id} {$p->first_name} {$p->last_name} | email={$p->email} | status={$p->status} | commune={$p->commune} | wallet={$p->eco_wallet_balance}\n";
}
echo "\nTotal: " . $all->count() . "\n\n";

// Recréer le driver de test correctement
echo "=== Creating test driver ===\n";
$driver = Provider::updateOrCreate(
    ['email' => 'testdriver@picme.ci'],
    [
        'first_name' => 'Test',
        'last_name'  => 'Driver PDP',
        'mobile'     => '+2250700000099',
        'password'   => bcrypt('Test123!'),
        'status'     => 'approved',
        'latitude'   => 5.345317,
        'longitude'  => -4.024429,
        'commune'    => 'Cocody',
        'eco_wallet_balance' => 50000,
        'rating'     => 5.0,
    ]
);
echo "Driver ID: {$driver->id}\n";

// Assigner à tous les services
use App\ServiceType;
$services = ServiceType::all();
foreach ($services as $service) {
    ProviderService::updateOrCreate(
        ['provider_id' => $driver->id, 'service_type_id' => $service->id],
        ['status' => 'active', 'service_number' => 'CI-1234-AB', 'service_model' => 'Toyota Corolla']
    );
    echo "  Service {$service->name} (ID={$service->id}) - ASSIGNED\n";
}
echo "\nDone!\n";
