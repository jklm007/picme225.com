<?php
include 'vendor/autoload.php';
$app = include 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$provider = App\Provider::find(1);
$token = $provider->createToken('TestToken')->accessToken;

echo "Token: " . $token . "\n\n";

$request = Illuminate\Http\Request::create('/api/provider/profile', 'GET');
$request->headers->set('Authorization', 'Bearer ' . $token);
$request->headers->set('Accept', 'application/json');

// Simulate middleware
try {
    $response = Route::dispatch($request);
    echo "Response Status: " . $response->getStatusCode() . "\n";
    echo "Response Content: " . $response->getContent() . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
