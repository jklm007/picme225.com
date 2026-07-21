<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$provider = \App\Models\Provider::first();
if (!$provider) {
    $provider = \App\Models\Provider::create([
        'first_name' => 'Test', 
        'last_name' => 'Driver', 
        'email' => 'test@driver.com', 
        'password' => bcrypt('password')
    ]);
}

echo "Testing with Provider ID: " . $provider->id . "\n";

$request = new \Illuminate\Http\Request();
$request->replace(['message' => 'Je veux savoir comment voir mes trajets']);
\Auth::guard('providerapi')->setUser($provider);
\Auth::setUser($provider); // fallback

$controller = new \App\Http\Controllers\ProviderResources\TripController();
$response = $controller->sendSupportMessage($request);

echo "Response from sendSupportMessage:\n";
echo $response->getContent() . "\n";

echo "\nTesting History Endpoint:\n";
$historyResponse = $controller->getSupportHistory($request);
echo $historyResponse->getContent() . "\n";

