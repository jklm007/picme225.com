<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = \Illuminate\Http\Request::create('/api/user/service-types', 'GET', [
    'service_name' => 'rental',
    'ride_variant' => 'avec_chauffeur',
    's_latitude' => 5.345317,
    's_longitude' => -4.024429,
    'd_latitude' => '',
    'd_longitude' => ''
]);
$controller = new App\Http\Controllers\UserServiceController();
$response = $controller->getServiceTypes($request);
echo "RENTAL:\n";
echo $response->getContent() . "\n\n";

$request2 = \Illuminate\Http\Request::create('/api/user/service-types', 'GET', [
    'service_name' => 'urgence',
    'ride_variant' => 'ambulance',
    's_latitude' => 5.345317,
    's_longitude' => -4.024429,
    'd_latitude' => '',
    'd_longitude' => ''
]);
$response2 = $controller->getServiceTypes($request2);
echo "URGENCE:\n";
echo $response2->getContent() . "\n\n";
