<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$services = \App\Models\ServiceType::all();
foreach ($services as $st) {
    echo $st->name . " -> " . json_encode($st->allowed_variants) . "\n";
}
