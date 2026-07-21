<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$provider = "\App\Provider"::where('status', 'approved')->first();
if (!$provider) {
    echo "No provider found\n";
    exit;
}

echo "Provider ID: {$provider->id}\n";
$service = $provider->service;
if (!$service) {
    echo "No service\n";
    exit;
}
echo "Service status: {$service->status}\n";

$provider->load('service');
$json = json_encode($provider, JSON_PRETTY_PRINT);
echo "JSON response:\n$json\n";
