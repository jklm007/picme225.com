<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$services = \App\Service::all();
echo "Total Services: " . $services->count() . "\n";
foreach ($services as $s) {
    echo "ID: {$s->id} | Name: {$s->name} | Types: " . $s->serviceTypes->count() . "\n";
    foreach($s->serviceTypes as $st) {
        echo "  - Type ID: {$st->id} | Name: {$st->name} | Variants: " . json_encode($st->allowed_variants) . "\n";
    }
}
