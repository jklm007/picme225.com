<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Provider;
use App\UserRequests;
use App\Services\DispatchEngine\MatchingService;
use App\Services\DispatchEngine\GeoService;
use App\Services\DispatchEngine\ScoreService;
use App\Services\DispatchEngine\RoutingService;

// Setup mock services
$geo = new GeoService();
$routing = new RoutingService();
$score = new ScoreService($geo, $routing);
$matching = new MatchingService($geo, $score, $routing);

echo "Starting Dispatch Engine Test...\n";
echo "====================================\n";

// Ensure we have some providers
$providers = Provider::take(2)->get();
if ($providers->count() < 2) {
    echo "Need at least 2 providers in DB to run this test.\n";
    exit;
}

$driverA = $providers[0];
$driverA->status = 'approved';
$driverA->save();

$driverB = $providers[1];
$driverB->status = 'approved';
$driverB->save();

// Clean up existing requests for these drivers
UserRequests::whereIn('provider_id', [$driverA->id, $driverB->id])->delete();

// Scenario 1: Driver A is on a PRIVATE ride (Chained expected)
echo "Setting up Driver A (Private Ride)...\n";
$reqA = new UserRequests();
$reqA->booking_id = 'TEST_'.uniqid();
$reqA->user_id = 1;
$reqA->provider_id = $driverA->id;
$reqA->current_provider_id = $driverA->id;
$reqA->service_type_id = 1;
$reqA->status = 'PICKEDUP';
$reqA->ride_variant = 'prive';
$reqA->s_latitude = 5.359951; // Abidjan
$reqA->s_longitude = -4.008256;
$reqA->d_latitude = 5.345317; // Plateau
$reqA->d_longitude = -4.024429;
$reqA->save();

// Scenario 2: Driver B is on a SHARED ride with seats available (Pooling expected)
echo "Setting up Driver B (Shared Ride with seats)...\n";
$reqB = new UserRequests();
$reqB->booking_id = 'TEST_'.uniqid();
$reqB->user_id = 2;
$reqB->provider_id = $driverB->id;
$reqB->current_provider_id = $driverB->id;
$reqB->service_type_id = 1;
$reqB->status = 'PICKEDUP';
$reqB->ride_variant = 'dynamique';
$reqB->total_capacity = 4;
$reqB->seats_booked = 1; // 3 seats left
$reqB->s_latitude = 5.359951;
$reqB->s_longitude = -4.008256;
$reqB->d_latitude = 5.345317;
$reqB->d_longitude = -4.024429;
$reqB->save();

// Set drivers current locations near the pickup point
$driverA->latitude = 5.360000;
$driverA->longitude = -4.008000;
$driverA->save();

$driverB->latitude = 5.360000;
$driverB->longitude = -4.008000;
$driverB->save();

echo "\n--- TEST 1: New request is PRIVATE ---\n";
$ctxPrivate = [
    's_lat' => 5.345317, // Near Driver A's dropoff
    's_lng' => -4.024429,
    'search_radius_km' => 10.0,
    'ride_variant' => 'prive'
];
$results = $matching->findBestDrivers($ctxPrivate);
foreach ($results as $res) {
    echo "Driver ID: {$res->id} | Dispatch Type: {$res->_dispatch_type} | Score: {$res->_dispatch_score}\n";
}

echo "\n--- TEST 2: New request is SHARED (Dynamique) ---\n";
$ctxShared = [
    's_lat' => 5.360000, // Near Driver B's current location
    's_lng' => -4.008000,
    'search_radius_km' => 10.0,
    'ride_variant' => 'dynamique'
];
$results = $matching->findBestDrivers($ctxShared);
foreach ($results as $res) {
    echo "Driver ID: {$res->id} | Dispatch Type: {$res->_dispatch_type} | Score: {$res->_dispatch_score}\n";
}

echo "\nTest Complete.\n";
