<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// simulate the API endpoint
// From ProviderApiController or UserApiController
// It probably does something like ServiceTypeRental::where('service_type_id', $id)->with('km_hour')->get()
$serviceTypeId = 10; // Berline
$rentals = \Illuminate\Support\Facades\DB::table('service_type_rentals')
    ->join('km_hours', 'service_type_rentals.km_hour_id', '=', 'km_hours.id')
    ->where('service_type_rentals.service_type_id', $serviceTypeId)
    ->get();

echo "Packages for Berline (10):\n";
echo json_encode($rentals, JSON_PRETTY_PRINT);
