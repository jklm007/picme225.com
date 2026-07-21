<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Provider;
use App\ProviderService;
use App\ServiceType;

// We need an active provider
$provider = Provider::firstOrCreate(
    ['email' => 'test_dummy_driver@picme.com'],
    [
        'first_name' => 'Dummy',
        'last_name' => 'Driver',
        'mobile' => '0000000000',
        'password' => bcrypt('password'),
        'status' => 'approved',
        'latitude' => 5.34843, // Abidjan center
        'longitude' => -4.02442,
        'commune' => 'Cocody' // Fixed 'commune' error
    ]
);

// Force active and location
$provider->status = 'approved';
$provider->latitude = 5.34843;
$provider->longitude = -4.02442;
$provider->save();

// Assign missing service types
$types = ServiceType::whereIn('name', ['Berline Voyage', 'SUV Voyage', 'MiniBus Voyage', 'Dépanneuse', 'Berline', 'Ambulance', 'Moto', 'Cargo', 'SUV'])->get();

foreach ($types as $type) {
    ProviderService::updateOrCreate(
        ['provider_id' => $provider->id, 'service_type_id' => $type->id],
        [
            'status' => 'active',
            'service_number' => 'TEST-1234',
            'service_model' => 'Test Model'
        ]
    );
    echo "Assigned " . $type->name . " to dummy provider.\n";
}

echo "Created/Updated dummy provider to ensure vehicles show up in the app!\n";
