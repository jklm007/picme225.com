<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$controller = new App\Http\Controllers\UserServiceController();

// Test 1: Coordinates omitted
$request1 = \Illuminate\Http\Request::create('/api/user/service-types', 'GET', [
    'service_name' => 'rental',
    'ride_variant' => 'avec_chauffeur'
]);
$response1 = $controller->getServiceTypes($request1);
echo "OMITTED:\n";
echo $response1->getContent() . "\n\n";

// Test 2: Coordinates empty strings
$request2 = \Illuminate\Http\Request::create('/api/user/service-types', 'GET', [
    'service_name' => 'rental',
    'ride_variant' => 'avec_chauffeur',
    's_latitude' => '',
    's_longitude' => ''
]);
$response2 = $controller->getServiceTypes($request2);
echo "EMPTY STRINGS:\n";
echo $response2->getContent() . "\n\n";
