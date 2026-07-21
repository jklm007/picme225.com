<?php
include 'vendor/autoload.php';
$app = include 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$provider = App\Provider::find(1);
Auth::guard('providerapi')->setUser($provider);
Auth::loginUsingId(1); // Set for default Auth facade
$trip = (new App\Http\Controllers\ProviderResources\TripController())->index(new Illuminate\Http\Request());
echo json_encode($trip, JSON_PRETTY_PRINT);
