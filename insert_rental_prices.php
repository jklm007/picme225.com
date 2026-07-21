<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$prices = [
    // Berline (ID: 10)
    10 => [
        1 => 5000,
        2 => 8000,
        3 => 12000,
        4 => 20000,
        5 => 25000,
        6 => 30000
    ],
    // Pick-up (ID: 11)
    11 => [
        1 => 6000,
        2 => 10000,
        3 => 15000,
        4 => 25000,
        5 => 32000,
        6 => 40000
    ],
    // Tricycle (ID: 12)
    12 => [
        1 => 2000,
        2 => 3500,
        3 => 6000,
        4 => 10000,
        5 => 12000,
        6 => 15000
    ],
    // Van (ID: 13)
    13 => [
        1 => 8000,
        2 => 14000,
        3 => 20000,
        4 => 35000,
        5 => 40000,
        6 => 50000
    ],
    // Car voyage (ID: 14)
    14 => [
        1 => 15000,
        2 => 25000,
        3 => 45000,
        4 => 70000,
        5 => 85000,
        6 => 100000
    ]
];

$now = now();

foreach ($prices as $serviceTypeId => $packages) {
    foreach ($packages as $kmHourId => $price) {
        $existing = DB::table('service_type_rentals')
            ->where('service_type_id', $serviceTypeId)
            ->where('km_hour_id', $kmHourId)
            ->first();
            
        if ($existing) {
            DB::table('service_type_rentals')
                ->where('id', $existing->id)
                ->update(['ren_price' => $price, 'updated_at' => $now]);
        } else {
            DB::table('service_type_rentals')->insert([
                'service_type_id' => $serviceTypeId,
                'km_hour_id' => $kmHourId,
                'ren_price' => $price,
                'created_at' => $now,
                'updated_at' => $now
            ]);
        }
    }
}

echo "Rental prices inserted successfully!\n";
