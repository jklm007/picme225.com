<?php
include 'vendor/autoload.php';
$app = include 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$provider = App\Provider::find(1);
Auth::guard('providerapi')->setUser($provider);
$profile = (new App\Http\Controllers\ProviderResources\ProfileController())->index();
echo json_encode($profile, JSON_PRETTY_PRINT);
