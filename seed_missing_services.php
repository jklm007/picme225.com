<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\ServiceType;

// 1. Update existing Delivery (id 5, 6) to have ["prive", "partage"]
ServiceType::whereIn('id', [5, 6])->update([
    'allowed_variants' => json_encode(["prive", "partage"])
]);
echo "Updated Delivery variants.\n";

// 2. Update existing Rental (id 8) to have ["avec_chauffeur", "sans_chauffeur"]
ServiceType::where('id', 8)->update([
    'allowed_variants' => json_encode(["avec_chauffeur", "sans_chauffeur"])
]);
echo "Updated Rental SUV variants.\n";

// Add a Berline Rental if it doesn't exist
if (!ServiceType::where('name', 'Berline')->where('type', 'rental')->exists()) {
    ServiceType::create([
        'name' => 'Berline',
        'provider_name' => 'Berline Location',
        'image' => '',
        'marker' => '',
        'capacity' => 4,
        'fixed' => 20000,
        'price' => 2000,
        'minute' => 0,
        'hour' => 0,
        'distance' => 0,
        'calculator' => 'DISTANCE',
        'description' => 'Location Berline',
        'status' => 1,
        'type' => 'rental',
        'allowed_variants' => ["avec_chauffeur", "sans_chauffeur"]
    ]);
    echo "Created Berline Rental.\n";
}

// 3. Update Ambulance
ServiceType::where('id', 9)->update([
    'type' => 'urgence',
    'allowed_variants' => json_encode(["ambulance"])
]);
echo "Updated Ambulance type and variants.\n";

// Add Depanneuse if it doesn't exist
if (!ServiceType::where('name', 'Dépanneuse')->where('type', 'urgence')->exists()) {
    ServiceType::create([
        'name' => 'Dépanneuse',
        'provider_name' => 'Remorquage',
        'image' => '',
        'marker' => '',
        'capacity' => 2,
        'fixed' => 15000,
        'price' => 500,
        'minute' => 0,
        'hour' => 0,
        'distance' => 500,
        'calculator' => 'DISTANCE',
        'description' => 'Service de dépannage auto',
        'status' => 1,
        'type' => 'urgence',
        'allowed_variants' => ["depannage"]
    ]);
    echo "Created Dépanneuse Urgence.\n";
}

// 4. Voyage services
$voyageServices = [
    ['name' => 'Berline Voyage', 'cap' => 4, 'fixed' => 15000, 'price' => 250],
    ['name' => 'SUV Voyage', 'cap' => 6, 'fixed' => 20000, 'price' => 300],
    ['name' => 'MiniBus Voyage', 'cap' => 14, 'fixed' => 30000, 'price' => 200]
];

foreach ($voyageServices as $vs) {
    if (!ServiceType::where('name', $vs['name'])->where('type', 'voyage')->exists()) {
        ServiceType::create([
            'name' => $vs['name'],
            'provider_name' => $vs['name'],
            'image' => '',
            'marker' => '',
            'capacity' => $vs['cap'],
            'fixed' => $vs['fixed'],
            'price' => $vs['price'],
            'minute' => 0,
            'hour' => 0,
            'distance' => $vs['price'],
            'calculator' => 'DISTANCE',
            'description' => 'Service de voyage interurbain',
            'status' => 1,
            'type' => 'voyage',
            'allowed_variants' => ["prive", "partage"]
        ]);
        echo "Created ".$vs['name']."\n";
    }
}

echo "Done seeding service types!\n";
