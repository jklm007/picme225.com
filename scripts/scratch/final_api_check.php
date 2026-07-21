<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\UserApiController;
use Illuminate\Http\Request;

$controller = new UserApiController();

// CASE 1: Taxi, Prive, No coordinates (Typical first load)
echo "CASE 1: Taxi, Prive, No GPS\n";
$request = new Request([
    'service_name' => 'standard',
    'ride_variant' => 'prive'
]);
$response = $controller->getServiceTypes($request);
$data = json_decode($response->getContent(), true);
echo "Status: " . ($data['status'] ? "SUCCESS" : "FAILED") . "\n";
echo "Count: " . (isset($data['service']['service_types']) ? count($data['service']['service_types']) : 0) . "\n";
foreach ($data['service']['service_types'] ?? [] as $st) {
    echo "  - [{$st['id']}] {$st['name']}\n";
}

// CASE 2: Livraison, Dynamique
echo "\nCASE 2: Livraison, Dynamique\n";
$request = new Request([
    'service_name' => 'delivery',
    'ride_variant' => 'dynamique'
]);
$response = $controller->getServiceTypes($request);
$data = json_decode($response->getContent(), true);
echo "Count: " . (isset($data['service']['service_types']) ? count($data['service']['service_types']) : 0) . "\n";
foreach ($data['service']['service_types'] ?? [] as $st) {
    echo "  - [{$st['id']}] {$st['name']}\n";
}

// CASE 3: Unknown service name (Simulate translation bug)
echo "\nCASE 3: Unknown name 'Urgence Médicale'\n";
$request = new Request([
    'service_name' => 'urgence médicale',
    'ride_variant' => 'prive'
]);
$response = $controller->getServiceTypes($request);
$data = json_decode($response->getContent(), true);
echo "Status: " . ($data['status'] ? "SUCCESS" : "FAILED") . "\n";
if (!$data['status']) echo "Message: {$data['message']}\n";
