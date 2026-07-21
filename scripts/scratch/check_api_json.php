<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\UserApiController;
use Illuminate\Http\Request;

$controller = new UserApiController();

$request = new Request([
    'service_name' => 'standard',
    'ride_variant' => 'prive'
]);

$response = $controller->getServiceTypes($request);
$data = json_decode($response->getContent(), true);

foreach ($data['service']['service_types'] as $st) {
    echo "Type: {$st['name']} | ID: {$st['id']} | Ambulance: " . ($st['ambulance'] ?? 'MISSING') . "\n";
}
