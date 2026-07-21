<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Http\Request;

echo "=== SIMULATING RENTAL SERVICE_TYPES API CALL ===" . PHP_EOL;
$requestRental = new Request([
    'service_name' => 'location',
    's_latitude' => '5.3484',
    's_longitude' => '-4.0305',
    'ride_variant' => 'avec_chauffeur'
]);
$controller = new App\Http\Controllers\UserServiceController();
$responseRental = $controller->getServiceTypes($requestRental);
echo "Status code: " . $responseRental->getStatusCode() . PHP_EOL;
echo "Response body: " . substr($responseRental->getContent(), 0, 1000) . PHP_EOL;
