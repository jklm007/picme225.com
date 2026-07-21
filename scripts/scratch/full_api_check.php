<?php
include 'vendor/autoload.php';
$app = include 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$mobile = '8465562222';
$provider = App\Provider::where('mobile', $mobile)->first();

if (!$provider) {
    die("Provider not found\n");
}

Auth::guard('providerapi')->setUser($provider);
Auth::loginUsingId($provider->id);

echo "--- Profile API ---\n";
$profile = (new App\Http\Controllers\ProviderResources\ProfileController())->index();
echo json_encode($profile, JSON_PRETTY_PRINT) . "\n\n";

echo "--- Trip API ---\n";
$trip = (new App\Http\Controllers\ProviderResources\TripController())->index(new Illuminate\Http\Request());
echo json_encode($trip, JSON_PRETTY_PRINT) . "\n\n";

echo "--- Documents API ---\n";
$docs = (new App\Http\Controllers\ProviderResources\DocumentController())->index();
echo json_encode($docs->original, JSON_PRETTY_PRINT) . "\n\n";
