<?php
include 'vendor/autoload.php';
$app = include 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$provider = App\Provider::find(1);
Auth::guard('providerapi')->setUser($provider);
$docs = (new App\Http\Controllers\ProviderResources\DocumentController())->index();
echo json_encode($docs->original, JSON_PRETTY_PRINT);
