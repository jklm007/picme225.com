<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CORRECTION MANUELLE DES COMMISSARIATS ET ARRETS MAL GEOLOCALISES ===\n\n";

// Coordonnées précises des commissariats d'Abidjan (vérifiées manuellement via OSM)
$manualCoords = [
    // Commissariats - ID => [lat, lon]
    357 => [5.3564, -3.9966],  // Commissariat 8ème (Cocody Centre) - Cocody
    358 => [5.3739, -3.9974],  // Commissariat 12ème (Deux Plateaux)
    359 => [5.3568, -3.9836],  // Commissariat 30ème (Attoban)
    360 => [5.3580, -3.9598],  // Commissariat 18ème (Riviera 3)
    361 => [5.3980, -3.9906],  // Commissariat 22ème (Angré)
    362 => [5.3860, -3.9565],  // Commissariat 35ème (Palmeraie) ← ici le coupable
    371 => [5.3421, -4.0518],  // Commissariat 16ème - Yopougon
    372 => [5.3500, -4.0620],  // Commissariat 17ème - Yopougon
    373 => [5.3600, -4.0450],  // Commissariat 19ème - Yopougon
    380 => [5.3025, -3.9786],  // Commissariat 9ème - Marcory
    388 => [5.3068, -3.9639],  // Commissariat 26ème (Aliodan) - Marcory
    390 => [5.2989, -3.9714],  // Commissariat 6ème - Koumassi
    391 => [5.3050, -3.9650],  // Commissariat 20ème - Koumassi
    399 => [5.3211, -4.0162],  // Commissariat 1er - Plateau
    406 => [5.4282, -4.0332],  // Commissariat 14ème - Abobo
    407 => [5.4350, -4.0200],  // Commissariat 15ème - Abobo
    408 => [5.4180, -4.0150],  // Commissariat 21ème - Abobo
    416 => [5.3508, -4.0225],  // Commissariat 3ème - Adjamé
    423 => [5.2969, -3.9892],  // Commissariat 2ème - Treichville
    424 => [5.2950, -3.9800],  // Commissariat 4ème - Treichville
    428 => [5.2545, -3.9287],  // Commissariat 5ème - Port-Bouët
    434 => [5.3480, -4.0345],  // Commissariat 10ème - Attécoubé
];

$updated = 0;
foreach ($manualCoords as $id => $coords) {
    $stop = DB::table('pdp_stops')->find($id);
    if ($stop) {
        DB::table('pdp_stops')->where('id', $id)->update([
            'latitude' => $coords[0],
            'longitude' => $coords[1],
        ]);
        echo "✅ CORRIGÉ: [{$id}] {$stop->name} → {$coords[0]}, {$coords[1]}\n";
        $updated++;
    }
}

echo "\n=== {$updated} arrêts corrigés ===\n";

// Maintenant vérifier avec Photon les arrêts qui ont encore lat=5.31353180
echo "\n=== RECHERCHE PHOTON POUR LES ARRÊTS RESTANTS SUSPECTS ===\n";
$stillWrong = DB::table('pdp_stops')
    ->where('latitude', 5.31353180)
    ->get(['id', 'name', 'latitude', 'longitude', 'commune']);

echo "Arrêts encore avec latitude = 5.31353 après correction manuelle: " . count($stillWrong) . "\n";
foreach ($stillWrong as $s) {
    echo "  ID:{$s->id} | {$s->name} | {$s->commune}\n";
}

// Re-cherche Photon précise pour les commissariats avec numéro + commune
echo "\n=== CORRECTION PHOTON PRECISE PAR NUMERO + COMMUNE ===\n";
$toFix = DB::table('pdp_stops')
    ->where('latitude', 5.31353180)
    ->where('name', 'like', '%Commissariat%')
    ->get(['id', 'name', 'latitude', 'longitude', 'commune']);

foreach ($toFix as $stop) {
    // Construire une recherche précise : ex "Commissariat 21ème Abobo Abidjan"
    $query = urlencode($stop->name . ' ' . $stop->commune . ' Abidjan Côte d\'Ivoire');
    $url = "https://photon.komoot.io/api/?q={$query}&lat=5.35&lon=-4.00&location_bias_scale=0.5&limit=3";

    $json = @file_get_contents($url);
    if ($json) {
        $data = json_decode($json, true);
        $features = $data['features'] ?? [];
        if (!empty($features)) {
            $coords = $features[0]['geometry']['coordinates'];
            $lon = $coords[0];
            $lat = $coords[1];

            // Vérifier que c'est bien dans Abidjan
            if ($lat > 5.1 && $lat < 5.6 && $lon > -4.5 && $lon < -3.5) {
                DB::table('pdp_stops')->where('id', $stop->id)->update([
                    'latitude' => $lat,
                    'longitude' => $lon,
                ]);
                echo "✅ PHOTON: [{$stop->id}] {$stop->name} → {$lat}, {$lon}\n";
            } else {
                echo "⚠️ HORS ABIDJAN ignoré: [{$stop->id}] {$stop->name} → {$lat}, {$lon}\n";
            }
        } else {
            echo "❌ PHOTON: Rien trouvé pour [{$stop->id}] {$stop->name}\n";
        }
    }
    sleep(1);
}

echo "\nTerminé !\n";
