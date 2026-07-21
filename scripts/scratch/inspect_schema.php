<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

// Check providers table columns
$cols = Schema::getColumnListing('providers');
echo "providers columns:\n  " . implode(", ", $cols) . "\n\n";

// Check provider_services table columns
$cols2 = Schema::getColumnListing('provider_services');
echo "provider_services columns:\n  " . implode(", ", $cols2) . "\n\n";

// Check service_types
$cols3 = Schema::getColumnListing('service_types');
echo "service_types columns:\n  " . implode(", ", $cols3) . "\n\n";

// Show all service types with their categories
$services = \App\ServiceType::all(['id','name','type','is_communal','is_intercity']);
echo "All ServiceTypes:\n";
foreach ($services as $s) {
    echo "  ID={$s->id} name={$s->name} type={$s->type} communal={$s->is_communal}\n";
}
