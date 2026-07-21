<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$services = \App\ServiceType::all();
echo "Total Service Types: " . $services->count() . "\n";
foreach ($services as $s) {
    echo "ID: {$s->id} | Name: {$s->name} | Variants: " . json_encode($s->allowed_variants) . "\n";
}
