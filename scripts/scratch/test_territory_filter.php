<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\UserApiController;
use Illuminate\Http\Request;

$controller = new UserApiController();

// Coordinates for Abidjan (randomly picked)
$sLat = 5.3484; $sLng = -4.0305;
$dLat = 5.3484; $dLng = -4.0305; // Same location = communal

echo "--- Testing Communal Trip (Same Location) ---\n";
$request = new Request([
    'service_name' => 'standard',
    'ride_variant' => 'prive',
    's_latitude' => $sLat, 's_longitude' => $sLng,
    'd_latitude' => $dLat, 'd_longitude' => $dLng
]);
$response = $controller->getServiceTypes($request);
echo "Result: " . count(json_decode($response->getContent(), true)['service']['service_types']) . " types.\n";

echo "\n--- Testing Inter-communal (Far away) ---\n";
// Let's assume we have two PdpStops in different communes in the DB.
// But I don't know the DB content for PdpStops.
// Let's check PdpStops.
$stops = \App\PdpStop::limit(2)->get();
if ($stops->count() >= 2) {
    $s = $stops[0]; $e = $stops[1];
    echo "From {$s->commune} to {$e->commune}\n";
    $request = new Request([
        'service_name' => 'standard',
        'ride_variant' => 'prive',
        's_latitude' => $s->latitude, 's_longitude' => $s->longitude,
        'd_latitude' => $e->latitude, 'd_longitude' => $e->longitude
    ]);
    $response = $controller->getServiceTypes($request);
    echo "Result: " . count(json_decode($response->getContent(), true)['service']['service_types']) . " types.\n";
} else {
    echo "Not enough PdpStops to test inter-communal logic.\n";
}
