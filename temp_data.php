<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$s_types = \App\ServiceType::whereIn('name', ['UTB Express', 'SBTA Express'])->get();
foreach ($s_types as $st) {
    $st->description = 'Voyage interurbain et interrégional en car.';
    $st->max_distance = 1500;
    $st->calculator = 'DISTANCEMIN';
    $st->save();
    echo "Updated data for " . $st->name . "\n";
}
