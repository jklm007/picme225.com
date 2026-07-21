<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = \App\Service::where('name', 'taxi')->first();
echo "Search 'taxi' (lowercase): " . ($service ? "Found (ID: {$service->id})" : "NOT FOUND") . "\n";

$service2 = \App\Service::where('name', 'Taxi')->first();
echo "Search 'Taxi' (Capital): " . ($service2 ? "Found (ID: {$service2->id})" : "NOT FOUND") . "\n";
