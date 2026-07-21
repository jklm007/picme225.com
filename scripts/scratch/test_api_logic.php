<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\UserApiController;
use Illuminate\Http\Request;

$controller = new UserApiController();

$variants = ['prive', 'dynamique', 'arret'];
foreach ($variants as $v) {
    echo "--- Testing Variant: $v ---\n";
    $request = new Request([
        'service_name' => 'standard',
        'ride_variant' => $v
    ]);
    
    $response = $controller->getServiceTypes($request);
    $data = json_decode($response->getContent(), true);
    
    if ($data['status']) {
        echo "Found " . count($data['service']['service_types']) . " types.\n";
        foreach ($data['service']['service_types'] as $st) {
            echo "  - {$st['name']} (ID: {$st['id']})\n";
        }
    } else {
        echo "Error: " . $data['message'] . "\n";
    }
    echo "\n";
}
