<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

use App\PdpRouteSegment;
use App\Helpers\Helper;

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== RÉPARATION DES DISTANCES DANS LES SEGMENTS ===\n\n";

$segments = PdpRouteSegment::with(['fromStop', 'toStop'])->get();
$updated = 0;

foreach ($segments as $segment) {
    if (!$segment->fromStop || !$segment->toStop) {
        echo "⚠️  Segment #{$segment->id} : Arrêt manquant (Ignoré)\n";
        continue;
    }

    $dist = Helper::haversineGreatCircleDistance(
        $segment->fromStop->latitude,
        $segment->fromStop->longitude,
        $segment->toStop->latitude,
        $segment->toStop->longitude
    ) / 1000; // Kilomètres

    $dist = round($dist, 2);

    if ($dist == 0) $dist = 0.5; // Sécurité minimum 500m si les points sont identiques

    if ($segment->distance_km != $dist || is_null($segment->distance_km)) {
        $old = $segment->distance_km ?? 'NULL';
        $segment->distance_km = $dist;
        $segment->save();
        echo "✅ Segment #{$segment->id} : $old km -> $dist km\n";
        $updated++;
    }
}

echo "\nTerminé ! $updated segments ont été mis à jour.\n";
