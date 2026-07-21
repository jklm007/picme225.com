<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== VERIFICATION DES ARRETS SUSPECTS ===\n\n";

// Récupérer tous les arrêts et vérifier ceux dont les coordonnées semblent hors Abidjan
$stops = DB::table('pdp_stops')->get(['id','name','latitude','longitude','commune']);

$suspects = [];
foreach ($stops as $stop) {
    $lat = floatval($stop->latitude);
    $lon = floatval($stop->longitude);

    // Abidjan est entre lat 5.2 et 5.5, lon -4.3 et -3.6
    $isOutsideAbidjan = ($lat < 5.1 || $lat > 5.7 || $lon < -4.5 || $lon > -3.5);
    $isZero = ($lat == 0 || $lon == 0);

    if ($isOutsideAbidjan || $isZero) {
        $suspects[] = $stop;
    }
}

echo "Nombre d'arrêts avec coordonnées suspectes: " . count($suspects) . "\n\n";
foreach ($suspects as $s) {
    echo "ID:{$s->id} | {$s->name} | lat:{$s->latitude} | lon:{$s->longitude} | {$s->commune}\n";
}

echo "\n=== ARRETS CLES A VERIFIER ===\n";
$keyStops = DB::table('pdp_stops')
    ->where('name', 'like', '%Commissariat%')
    ->orWhere('name', 'like', '%Palmeraie%')
    ->orWhere('name', 'like', '%35%')
    ->get(['id','name','latitude','longitude','commune']);

foreach ($keyStops as $s) {
    echo "ID:{$s->id} | {$s->name} | lat:{$s->latitude} | lon:{$s->longitude} | {$s->commune}\n";
}
