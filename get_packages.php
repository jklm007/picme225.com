<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rentals = \Illuminate\Support\Facades\DB::table('service_types')->whereIn('id', [10, 11, 12, 13, 14])->get();
foreach ($rentals as $r) {
    echo "ID: {$r->id} | Name: {$r->name} | rental_amount (Sans Chauffeur): {$r->rental_amount}\n";
}
