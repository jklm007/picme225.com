<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

use App\Service;
use App\ServiceType;
use Illuminate\Support\Facades\DB;

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// 1. Assurer l'existence du Service "Ambulance"
$ambulanceService = Service::updateOrCreate(
    ['name' => 'Ambulance'],
    ['image' => 'http://picme225.com/assets/img/ambulance.png']
);

// 2. Créer des types d'ambulance
$ambulanceTypes = [
    [
        'name' => 'Ambulance Basique',
        'provider_name' => 'Transport Sanitaire',
        'fixed' => 5000,
        'price' => 200,
        'capacity' => 1,
        'ambulance' => 1,
        'calculator' => 'DISTANCE',
        'distance' => 1,
        'image' => 'http://picme225.com/assets/img/ambulance_basic.png'
    ],
    [
        'name' => 'VHC Médicalisé (SAMU)',
        'provider_name' => 'Assistance Médicale',
        'fixed' => 15000,
        'price' => 500,
        'capacity' => 1,
        'ambulance' => 1,
        'calculator' => 'DISTANCE',
        'distance' => 1,
        'image' => 'http://picme225.com/assets/img/ambulance_medical.png'
    ],
    [
        'name' => 'SMUR Adulte',
        'provider_name' => 'Réanimation',
        'fixed' => 25000,
        'price' => 1000,
        'capacity' => 1,
        'ambulance' => 1,
        'calculator' => 'DISTANCE',
        'distance' => 1,
        'image' => 'http://picme225.com/assets/img/smur.png'
    ]
];

foreach ($ambulanceTypes as $typeData) {
    $type = ServiceType::updateOrCreate(
        ['name' => $typeData['name']],
        $typeData
    );

    // Attacher au service ambulance via la table pivot si pas déjà fait
    if (!$ambulanceService->serviceTypes()->where('service_type_id', $type->id)->exists()) {
        $ambulanceService->serviceTypes()->attach($type->id, [
            'name' => $typeData['name'],
            'fixed' => $typeData['fixed'],
            'price' => $typeData['price'],
            'status' => 1,
            'ambulance' => 1,
            'minute' => 0,
            'distance' => 1,
            'calculator' => 'DISTANCE',
        ]);
    }
}

echo "Ambulance services and types seeded successfully.\n";
