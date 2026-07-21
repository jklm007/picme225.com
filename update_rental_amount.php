<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$amounts = [
    10 => 20000, // Berline
    11 => 30000, // Pick-up
    12 => 10000, // Tricycle
    13 => 40000, // Van
    14 => 80000  // Car voyage
];

foreach ($amounts as $id => $amount) {
    DB::table('service_types')
        ->where('id', $id)
        ->update(['rental_amount' => $amount]);
}

echo "Rental amounts updated successfully!\n";
$services = DB::table('service_types')->whereIn('id', [10, 11, 12, 13, 14])->get();
foreach ($services as $s) {
    echo "ID: {$s->id} | Name: {$s->name} | rental_amount: {$s->rental_amount}\n";
}
