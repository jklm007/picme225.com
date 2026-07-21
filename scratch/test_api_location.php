<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$controller = new \App\Http\Controllers\UserServiceController();

$requests = [
    ['service_name' => 'location', 'ride_variant' => 'avec_chauffeur'],
    ['service_name' => 'location', 'ride_variant' => 'sans_chauffeur'],
    ['service_name' => 'Location', 'ride_variant' => 'avec_chauffeur'],
    ['service_name' => 'Location', 'ride_variant' => 'sans_chauffeur'],
    ['service_name' => 'rental', 'ride_variant' => 'avec_chauffeur'],
    ['service_name' => 'rental', 'ride_variant' => 'sans_chauffeur'],
    ['service_name' => 'Location'],
];

foreach ($requests as $idx => $reqData) {
    echo "--- TEST " . ($idx + 1) . " : " . json_encode($reqData) . " ---\n";
    $req = new \Illuminate\Http\Request();
    $req->replace($reqData);
    try {
        $response = $controller->getServiceTypes($req);
        echo "Response code: " . $response->getStatusCode() . "\n";
        echo "Response JSON:\n" . json_encode(json_decode($response->getContent()), JSON_PRETTY_PRINT) . "\n\n";
    } catch (\Throwable $e) {
        echo "ERROR: " . $e->getMessage() . "\n\n";
    }
}
