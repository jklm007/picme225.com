<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rentals = DB::table('service_type_rentals')->get();
echo "Service Type Rentals:\n";
foreach ($rentals as $r) {
    echo "- ServiceType ID: {$r->service_type_id} | KmHour ID: {$r->km_hour_id} | Price: {$r->ren_price}\n";
}
