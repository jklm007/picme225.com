<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = App\Models\Service::where('name', 'location')->orWhere('name', 'rental')->first();
if ($service) {
    echo "Service ID: " . $service->id . "\n";
    echo "Linked Service Types:\n";
    foreach($service->serviceTypes as $st) {
        echo " - " . $st->id . " : " . $st->name . "\n";
    }
}

echo "\nAll Service Types with Sans Chauffeur:\n";
$sans = App\Models\ServiceType::where('name', 'like', '%sans chauffeur%')->get();
foreach($sans as $st) {
    echo " - " . $st->id . " : " . $st->name . " (allow_without_driver: " . $st->allow_without_driver . ")\n";
    echo "   Linked services: ";
    foreach($st->services as $s) {
        echo $s->name . ", ";
    }
    echo "\n";
}
