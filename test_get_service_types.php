<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\UserServiceController;
use Illuminate\Http\Request;

function callApi($serviceName, $variant = 'prive') {
    echo "=== Querying Service: $serviceName (Variant: $variant) ===\n";
    $request = Request::create('/api/user/service/types', 'GET', [
        'service_name' => $serviceName,
        'ride_variant' => $variant
    ]);
    
    $controller = new UserServiceController();
    $response = $controller->getServiceTypes($request);
    
    echo "Status code: " . $response->getStatusCode() . "\n";
    $content = json_decode($response->getContent(), true);
    if (isset($content['service']['service_types'])) {
        foreach ($content['service']['service_types'] as $st) {
            echo "  - ServiceType ID: {$st['id']} | Name: {$st['name']} | Type: {$st['type']}\n";
        }
    } else {
        echo "  - No service types returned or error: " . print_r($content, true) . "\n";
    }
    echo "\n";
}

callApi('Taxi');
callApi('Location');
callApi('Urgence');
callApi('Partage');
callApi('Partage', 'partage');
