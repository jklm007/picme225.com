<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Http\Request;

$controller = new \App\Http\Controllers\UserApiController();

function fakeRequest($serviceName, $variant) {
    $request = Request::create('/api/user/service-types', 'GET', [
        'service_name' => $serviceName,
        'variant' => $variant,
        's_latitude' => 5.30966, // Abidjan coords
        's_longitude' => -4.01266,
        'd_latitude' => 5.34531,
        'd_longitude' => -4.02442,
    ]);
    // The controller gets injected request but we are calling manually, let's look at getServiceTypes in UserServiceController
    // Actually getServiceTypes is in UserServiceController!
    $userController = new \App\Http\Controllers\UserServiceController();
    $response = $userController->getServiceTypes($request);
    return json_decode($response->getContent(), true);
}

echo "Testing Location:\n";
print_r(fakeRequest('Location', 'avec_chauffeur'));

echo "Testing Urgence:\n";
print_r(fakeRequest('Urgence', 'ambulance'));

echo "Testing Taxi:\n";
print_r(fakeRequest('Taxi', 'prive'));

