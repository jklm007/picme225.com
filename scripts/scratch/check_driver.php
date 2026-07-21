<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Provider;
use App\ProviderService;
use App\ServiceType;

$driver = Provider::where('email', 'testdriver@picme.ci')->first();
echo "Driver ID: " . $driver->id . "\n";
echo "Status: " . $driver->status . "\n";
echo "Commune: " . $driver->commune . "\n";
echo "Eco Wallet: " . $driver->eco_wallet_balance . "\n\n";

// Show all service types
$services = ServiceType::all();
echo "Available ServiceTypes:\n";
foreach ($services as $s) {
    echo "  ID=" . $s->id . " Name=" . $s->name . " Type=" . $s->type . "\n";
}

// Assign driver to ALL service types for testing
echo "\nAssigning driver to all services...\n";
foreach ($services as $service) {
    $ps = ProviderService::firstOrNew([
        'provider_id' => $driver->id,
        'service_type_id' => $service->id
    ]);
    $ps->status = 'active';
    $ps->service_number = 'TEST-123';
    $ps->service_model = 'Toyota Corolla';
    $ps->save();
    echo "  Assigned: " . $service->name . " (ID=" . $service->id . ")\n";
}
echo "\nDone!\n";
