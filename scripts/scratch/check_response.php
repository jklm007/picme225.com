<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\UserApiController;
use Illuminate\Http\Request;

$controller = new UserApiController();

// Exact same request the app sends - no coordinates (user hasn't selected destination yet)
$request = new Request([
    'service_name' => 'Taxi',
    'ride_variant' => 'prive'
]);

$response = $controller->getServiceTypes($request);
$content = $response->getContent();
$data = json_decode($content, true);

echo "=== RAW JSON RESPONSE ===\n";
echo $content . "\n\n";
echo "=== PARSED ===\n";
echo "Keys in response: " . implode(', ', array_keys($data)) . "\n";
echo "status field value: " . var_export($data['status'] ?? 'KEY MISSING', true) . "\n";
echo "status field type: " . gettype($data['status'] ?? null) . "\n";
