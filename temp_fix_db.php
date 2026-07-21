<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Assign ServiceType 3 (Inter-communal) to Service 4 (Voyage)
DB::table('service_service_type')->insertOrIgnore([
    ['service_id' => 4, 'service_type_id' => 3],
    ['service_id' => 4, 'service_type_id' => 4], // SUV
]);

// Assign ServiceType 1, 2, 3, 4 to Service 6 (Partage)
DB::table('service_service_type')->insertOrIgnore([
    ['service_id' => 6, 'service_type_id' => 1],
    ['service_id' => 6, 'service_type_id' => 2],
    ['service_id' => 6, 'service_type_id' => 3],
    ['service_id' => 6, 'service_type_id' => 4],
]);

echo "DB updated.\n";
