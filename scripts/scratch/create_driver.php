<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Provider;
use App\ProviderService;
use App\ServiceType;

$driver = Provider::firstOrNew(['email' => 'testdriver@picme.ci']);
$driver->first_name = 'Test';
$driver->last_name = 'Driver';
$driver->mobile = '+2250102030405';
$driver->password = bcrypt('123456');
$driver->status = 'approved';
$driver->latitude = 5.345317;
$driver->longitude = -4.024429;
$driver->commune = 'Cocody';
$driver->eco_wallet_balance = 50000;
$driver->rating = 5.0;
$driver->save();

$services = ServiceType::all();
foreach ($services as $service) {
    if (in_array(strtolower($service->name), ['taxi', 'partage', 'share', 'woro-woro'])) {
        $ps = ProviderService::firstOrNew([
            'provider_id' => $driver->id,
            'service_type_id' => $service->id
        ]);
        $ps->status = 'active';
        $ps->service_number = 'TEST-123';
        $ps->service_model = 'Toyota Corolla';
        $ps->save();
        echo 'Assigned to Service: ' . $service->name . "\n";
    }
}
echo "Driver created.\n";
