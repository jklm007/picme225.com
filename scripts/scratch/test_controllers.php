<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TESTING SMART MODE CONTROLLER METHODS DIRECTLY ===\n";

$provider = App\Provider::find(12);

echo "\n--- 1. Testing GET /api/provider/pdp-routes ---\n";
try {
    $request = \Illuminate\Http\Request::create('/api/provider/pdp-routes', 'GET');
    $request->setUserResolver(function () use ($provider) { return $provider; });
    $controller = app(\App\Http\Controllers\UserApiController::class);
    $response = $controller->getPdpRoutes($request);
    echo "Status: " . $response->getStatusCode() . "\n";
    echo "Content: " . substr($response->getContent(), 0, 200) . "...\n";
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\n--- 2. Testing POST /api/provider/smart-mode ---\n";
try {
    $data = [
        'is_smart_mode' => '1',
        'smart_mode_type' => 'HOME',
        'smart_dest_lat' => '5.36',
        'smart_dest_lng' => '-4.02',
        'smart_dest_address' => 'Cocody',
        'smart_zone_radius' => '5',
        'smart_communes' => '[]'
    ];
    $request = \Illuminate\Http\Request::create('/api/provider/smart-mode', 'POST', $data);
    $request->setUserResolver(function () use ($provider) { return $provider; });
    // IMPORTANT: Temporarily mock Auth facade for the controller
    Auth::shouldReceive('guard')->with('providerapi')->andReturn((object)['user' => function() use ($provider) { return $provider; }]);
    
    $controller = app(\App\Http\Controllers\ProviderResources\ProfileController::class);
    $response = $controller->update_smart_mode($request);
    
    if (method_exists($response, 'getStatusCode')) {
        echo "Status: " . $response->getStatusCode() . "\n";
        echo "Content: " . substr($response->getContent(), 0, 200) . "...\n";
    } else {
        echo "Response returned: " . print_r($response, true) . "\n";
    }
} catch (\Illuminate\Validation\ValidationException $e) {
    echo "❌ VALIDATION ERROR:\n";
    print_r($e->errors());
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
