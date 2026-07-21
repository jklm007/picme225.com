<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

use App\PdpRoute;
use App\PdpStop;
use App\Http\Controllers\UserSharedRideController;
use Illuminate\Http\Request;

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TEST BI-DIRECTIONNEL PDP ===\n\n";

// Prendre la première route (Cocody - Angré par exemple)
$route = PdpRoute::with(['stops' => function($q) { $q->orderBy('order'); }])->first();

if (!$route || $route->stops->count() < 2) {
    die("Pas assez de données pour le test.\n");
}

$firstStop = $route->stops->first();
$lastStop = $route->stops->last();

echo "Route: {$route->name}\n";
echo "Départ: {$firstStop->name} (Ordre: {$firstStop->order})\n";
echo "Arrivée: {$lastStop->name} (Ordre: {$lastStop->order})\n\n";

$controller = new UserSharedRideController();

// 1. TEST SENS ALLER
echo "--- TEST SENS ALLER ({$firstStop->name} -> {$lastStop->name}) ---\n";
try {
    $request = new Request([
        'pdp_route_id' => $route->id,
        'start_stop_id' => $firstStop->id,
        'end_stop_id' => $lastStop->id,
    ]);
    $response = $controller->calculatePrice($request);
    echo "Résultat: " . json_encode(json_decode($response->getContent()), JSON_PRETTY_PRINT) . "\n\n";
} catch (Exception $e) {
    echo "ERREUR SENS ALLER: " . $e->getMessage() . "\n\n";
}

// 2. TEST SENS RETOUR
echo "--- TEST SENS RETOUR ({$lastStop->name} -> {$firstStop->name}) ---\n";
try {
    $request = new Request([
        'pdp_route_id' => $route->id,
        'start_stop_id' => $lastStop->id,
        'end_stop_id' => $firstStop->id,
    ]);
    $response = $controller->calculatePrice($request);
    echo "Résultat: " . json_encode(json_decode($response->getContent()), JSON_PRETTY_PRINT) . "\n\n";
} catch (Exception $e) {
    echo "ERREUR SENS RETOUR: " . $e->getMessage() . "\n\n";
}
