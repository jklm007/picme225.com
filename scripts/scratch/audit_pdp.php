<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

use App\PdpRoute;

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$routes = PdpRoute::with(['stops', 'segments'])->get();

foreach ($routes as $route) {
    echo "Ligne: {$route->name}\n";
    foreach ($route->segments as $seg) {
        if (is_null($seg->distance_km) || $seg->distance_km == 0) {
            echo "  - SEGMENT NULL/0 find! ID: {$seg->id} (Order: {$seg->order}) Dist: " . ($seg->distance_km ?? 'NULL') . "\n";
        }
    }
}
