<?php
/**
 * Test du RoutingService (Phase 4)
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\DispatchEngine\RoutingService;

$routing = new RoutingService();

echo "\n================================================\n";
echo "   TEST ROUTING SERVICE (OSRM/MAPBOX/GOOGLE)    \n";
echo "================================================\n\n";

// Cocody (5.3484, -4.0305) -> Plateau (5.3262, -4.0195)
$lat1 = 5.3484; $lng1 = -4.0305;
$lat2 = 5.3262; $lng2 = -4.0195;

echo "Demande d'itinéraire: Cocody -> Plateau...\n";

$startMs = microtime(true);
$route = $routing->getRouteEstimate($lat1, $lng1, $lat2, $lng2);
$elapsedMs = round((microtime(true) - $startMs) * 1000, 2);

if ($route) {
    echo "  [OK] Itinéraire trouvé en {$elapsedMs} ms\n";
    echo "       Distance : " . round($route['distance_km'], 2) . " km\n";
    echo "       Durée    : " . round($route['duration_min'], 2) . " min\n";
} else {
    echo "  [FAIL] Aucun itinéraire trouvé. Vérifiez les clés API ou la connexion internet.\n";
}

echo "\nTerminé.\n";
