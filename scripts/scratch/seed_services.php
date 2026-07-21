<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Services to ensure exist
$newServices = [
    [
        'name' => 'Livraison',
        'provider_name' => 'Livraison',
        'image' => 'http://picme225.com/storage/services/delivery.png',
        'fixed' => 1000,
        'price' => 500,
        'description' => 'Service de livraison de colis',
        'status' => 1,
        'calculator' => 'DISTANCE',
        'minute' => 0,
        'hour' => 0,
        'distance' => 10,
        'capacity' => 1,
        'is_taxable' => 1,
        'is_communal' => 0,
        'max_distance' => 50
    ],
    [
        'name' => 'Location',
        'provider_name' => 'Location',
        'image' => 'http://picme225.com/storage/services/rental.png',
        'fixed' => 5000,
        'price' => 0,
        'description' => 'Location de véhicule à l\'heure ou jour',
        'status' => 1,
        'calculator' => 'HOUR',
        'minute' => 0,
        'hour' => 5000,
        'distance' => 0,
        'capacity' => 4,
        'rental_amount' => 5000,
        'is_taxable' => 1,
        'is_communal' => 0,
        'max_distance' => 0
    ],
    [
        'name' => 'Voyage',
        'provider_name' => 'Voyage',
        'image' => 'http://picme225.com/storage/services/travel.png',
        'outstation_price' => 100,
        'fixed' => 2000,
        'price' => 100,
        'description' => 'Transport inter-ville',
        'status' => 1,
        'calculator' => 'DISTANCE',
        'minute' => 0,
        'hour' => 0,
        'distance' => 100,
        'capacity' => 4,
        'is_taxable' => 1,
        'is_communal' => 0,
        'max_distance' => 500
    ]
];

foreach ($newServices as $data) {
    $service = App\ServiceType::updateOrCreate(
        ['name' => $data['name']],
        $data
    );
    echo "Processed: " . $service->name . "\n";
}
