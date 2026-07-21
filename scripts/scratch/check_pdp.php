<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// 1. Doublons par nom
$dups = Illuminate\Support\Facades\DB::select(
    "SELECT name, COUNT(*) as cnt FROM pdp_stops GROUP BY name HAVING cnt > 1 ORDER BY cnt DESC"
);
echo "\n=== DOUBLONS PAR NOM ===\n";
if (empty($dups)) {
    echo "Aucun doublon de nom trouvé.\n";
} else {
    foreach ($dups as $d) {
        echo "  [{$d->cnt}x] {$d->name}\n";
    }
}

// 2. Doublons par coordonnées proches (GPS)
$gps_dups = Illuminate\Support\Facades\DB::select(
    "SELECT a.id as id1, b.id as id2, a.name as n1, b.name as n2, a.latitude, a.longitude
     FROM pdp_stops a JOIN pdp_stops b ON a.id < b.id
     WHERE ABS(a.latitude - b.latitude) < 0.001 AND ABS(a.longitude - b.longitude) < 0.001"
);
echo "\n=== DOUBLONS PAR GPS (< 100m) ===\n";
if (empty($gps_dups)) {
    echo "Aucun doublon GPS trouvé.\n";
} else {
    foreach ($gps_dups as $d) {
        echo "  ID#{$d->id1} [{$d->n1}] ≈ ID#{$d->id2} [{$d->n2}] @ {$d->latitude},{$d->longitude}\n";
    }
}

// 3. Total arrêts
$total = Illuminate\Support\Facades\DB::select("SELECT COUNT(*) as cnt FROM pdp_stops")[0]->cnt;
$routes = Illuminate\Support\Facades\DB::select("SELECT id, name FROM pdp_routes ORDER BY id");
echo "\n=== RÉSUMÉ ===\n";
echo "  Total arrêts en BDD: {$total}\n";
echo "  Lignes (pdp_routes): " . count($routes) . "\n";
foreach ($routes as $r) {
    $sc = Illuminate\Support\Facades\DB::select("SELECT COUNT(*) as cnt FROM pdp_route_stops WHERE pdp_route_id = {$r->id}")[0]->cnt;
    echo "    - Ligne #{$r->id} '{$r->name}': {$sc} arrêts associés\n";
}
echo "\nScript terminé.\n";
