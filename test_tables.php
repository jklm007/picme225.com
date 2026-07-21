<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$t1 = DB::table('km_hour_service_type_prices')->whereIn('service_type_id', [10, 11, 12, 13, 14])->get();
echo "km_hour_service_type_prices count: " . count($t1) . "\n";

$t2 = DB::table('service_type_rentals')->whereIn('service_type_id', [10, 11, 12, 13, 14])->get();
echo "service_type_rentals count: " . count($t2) . "\n";
