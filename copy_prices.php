<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "Schema of km_hour_service_type_prices:\n";
$columns = Schema::getColumnListing('km_hour_service_type_prices');
print_r($columns);

// Copy from service_type_rentals to km_hour_service_type_prices
$rentals = DB::table('service_type_rentals')->get();
foreach ($rentals as $rental) {
    DB::table('km_hour_service_type_prices')->updateOrInsert(
        ['km_hour_id' => $rental->km_hour_id, 'service_type_id' => $rental->service_type_id],
        ['price' => $rental->ren_price, 'created_at' => now(), 'updated_at' => now()]
    );
}

echo "Data copied successfully.\n";
