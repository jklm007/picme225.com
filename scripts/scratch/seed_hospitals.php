<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

use App\Hospital;
use Illuminate\Support\Facades\DB;

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$hospitals = [
    [
        'hospital_address' => 'Hôpital Militaire d’Abidjan (HMA)',
        'latitude' => 5.3783,
        'longitude' => -4.0125,
    ],
    [
        'hospital_address' => 'CHU de Cocody',
        'latitude' => 5.3458,
        'longitude' => -3.9875,
    ],
    [
        'hospital_address' => 'CHU de Treichville',
        'latitude' => 5.3056,
        'longitude' => -4.0042,
    ],
    [
        'hospital_address' => 'Polyclinique Internationale Sainte Anne-Marie (PISAM)',
        'latitude' => 5.3347,
        'longitude' => -3.9961,
    ],
    [
        'hospital_address' => 'CHU d’Angré',
        'latitude' => 5.4056,
        'longitude' => -3.9652,
    ]
];

foreach ($hospitals as $data) {
    Hospital::updateOrCreate(
        ['hospital_address' => $data['hospital_address']],
        [
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
        ]
    );
    echo "Seeded/Updated: " . $data['hospital_address'] . "\n";
}

echo "Seeding completed successfully.\n";
