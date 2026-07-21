<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\UserApiController;
use Illuminate\Http\Request;

$controller = new UserApiController();

// Simulate Abidjan coordinates (Plateau to Cocody)
// Plateau: 5.324, -4.020
// Cocody: 5.348, -3.989
$request = new Request([
    'service_name' => 'Taxi',
    'ride_variant' => 'prive',
    's_latitude' => 5.324,
    's_longitude' => -4.020,
    'd_latitude' => 5.348,
    'd_longitude' => -3.989
]);

echo "--- TESTING WITH COORDINATES (ABIDJAN) ---\n";
$response = $controller->getServiceTypes($request);
$data = json_decode($response->getContent(), true);

echo "Status: " . ($data['status'] ? "SUCCESS" : "FAILED") . "\n";
$types = $data['service']['service_types'] ?? [];
echo "Found " . count($types) . " types.\n";
foreach ($types as $t) {
    echo "  - [{$t['id']}] {$t['name']} (Communal: " . ($t['is_communal'] ? "YES" : "NO") . ")\n";
}
