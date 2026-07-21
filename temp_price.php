<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$s_types = \App\ServiceType::whereIn('name', ['UTB Express', 'SBTA Express'])->get();
foreach ($s_types as $st) {
    $st->fixed = 1000;
    $st->price = 100;
    $st->distance = 1;
    $st->save();
    echo "Updated prices for " . $st->name . "\n";
}
