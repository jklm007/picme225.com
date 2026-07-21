<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\ServiceType;

$types = ServiceType::all();
foreach ($types as $t) {
    echo "ID: " . $t->id . " - Name: " . $t->name . " (Capacity: " . $t->capacity . ")\n";
}
