<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\ServiceType;

$serviceTypes = ServiceType::all(['id', 'name']);
foreach ($serviceTypes as $st) {
    echo "ID: " . $st->id . " | Name: " . $st->name . "\n";
}
