<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$voyage = App\Models\Service::where('name', 'Voyage')->first();
if($voyage) {
    echo "Voyage Service ID: " . $voyage->id . "\n";
    foreach($voyage->serviceTypes as $st) {
        echo "- " . $st->name . "\n";
    }
} else {
    echo "Voyage not found.\n";
}

$partage = App\Models\Service::where('name', 'Partage')->first();
if($partage) {
    echo "Partage Service ID: " . $partage->id . "\n";
    foreach($partage->serviceTypes as $st) {
        echo "- " . $st->name . "\n";
    }
} else {
    echo "Partage not found.\n";
}
