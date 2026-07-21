<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\ServiceType;

$serviceTypes = ServiceType::all(['id', 'name', 'is_communal', 'is_intercity', 'type']);
foreach ($serviceTypes as $st) {
    echo "ID: " . $st->id . " | Name: " . $st->name . " | is_communal: " . $st->is_communal . " | is_intercity: " . $st->is_intercity . " | type: " . $st->type . "\n";
}
