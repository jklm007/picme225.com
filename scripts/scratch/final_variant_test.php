<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\UserApiController;
use Illuminate\Http\Request;

$controller = new UserApiController();

echo "--- FINAL TEST: TAXI VARIANTS ---\n";

$variants = ['prive', 'partage', 'arret_pdp'];

foreach ($variants as $v) {
    echo "\nTesting Variant: $v\n";
    $request = new Request([
        'service_name' => 'Taxi',
        'ride_variant' => $v
    ]);
    $response = $controller->getServiceTypes($request);
    $data = json_decode($response->getContent(), true);
    
    $types = $data['service']['service_types'] ?? [];
    echo "Found " . count($types) . " types.\n";
    foreach ($types as $t) {
        echo "  - {$t['name']} (ID: {$t['id']})\n";
    }
}

echo "\n--- FINAL TEST: NON-TAXI (LIVRAISON) ---\n";
$request = new Request([
    'service_name' => 'Livraison',
    'ride_variant' => 'partage' // Should be empty because Livraison is only prive
]);
$response = $controller->getServiceTypes($request);
$data = json_decode($response->getContent(), true);
$types = $data['service']['service_types'] ?? [];
echo "Livraison with 'partage' variant: " . count($types) . " types found (Expected: 0).\n";

$request = new Request([
    'service_name' => 'Livraison',
    'ride_variant' => 'prive'
]);
$response = $controller->getServiceTypes($request);
$data = json_decode($response->getContent(), true);
$types = $data['service']['service_types'] ?? [];
echo "Livraison with 'prive' variant: " . count($types) . " types found.\n";
