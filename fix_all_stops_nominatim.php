<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CORRECTION COMPLETE DES ARRETS VIA NOMINATIM (OSM) ===\n\n";

// Nominatim est plus précis que Photon pour les noms locaux d'Abidjan
// Il utilise la base OSM directement avec le géocodage structuré

$stops = DB::table('pdp_stops')
    ->whereNotNull('name')
    ->get(['id','name','latitude','longitude','commune']);

$updated = 0;
$failed = 0;

foreach ($stops as $stop) {
    // Construire une requête Nominatim structurée
    // city=Abidjan + country=CI pour forcer la zone géographique
    $q = urlencode($stop->name . ', ' . $stop->commune . ', Abidjan');
    $url = "https://nominatim.openstreetmap.org/search?q={$q}&countrycodes=ci&format=json&limit=1";
    
    $context = stream_context_create([
        'http' => [
            'header' => "User-Agent: PicmeApp/1.0 (contact@picme.ci)\r\n",
        ]
    ]);
    
    $r = @file_get_contents($url, false, $context);
    
    if ($r) {
        $results = json_decode($r, true);
        if (!empty($results)) {
            $lat = floatval($results[0]['lat']);
            $lon = floatval($results[0]['lon']);
            
            // Vérifier que c'est dans la zone d'Abidjan (étendue) ou Côte d'Ivoire
            if ($lat > 4.0 && $lat < 11.0 && $lon > -9.0 && $lon < -2.0) {
                DB::table('pdp_stops')->where('id', $stop->id)->update([
                    'latitude' => $lat,
                    'longitude' => $lon,
                ]);
                echo "✅ [{$stop->id}] {$stop->name} → {$lat}, {$lon}\n";
                $updated++;
            } else {
                echo "⚠️  [{$stop->id}] {$stop->name} → hors zone: {$lat}, {$lon} (ignoré)\n";
                $failed++;
            }
        } else {
            // Essai 2: recherche plus simple sans commune
            $q2 = urlencode($stop->name . ' Abidjan');
            $url2 = "https://nominatim.openstreetmap.org/search?q={$q2}&countrycodes=ci&format=json&limit=1";
            $r2 = @file_get_contents($url2, false, $context);
            
            if ($r2) {
                $results2 = json_decode($r2, true);
                if (!empty($results2)) {
                    $lat = floatval($results2[0]['lat']);
                    $lon = floatval($results2[0]['lon']);
                    
                    if ($lat > 4.0 && $lat < 11.0 && $lon > -9.0 && $lon < -2.0) {
                        DB::table('pdp_stops')->where('id', $stop->id)->update([
                            'latitude' => $lat,
                            'longitude' => $lon,
                        ]);
                        echo "✅ (retry) [{$stop->id}] {$stop->name} → {$lat}, {$lon}\n";
                        $updated++;
                    } else {
                        echo "❌ [{$stop->id}] {$stop->name} → INTROUVABLE (garde l'actuel: {$stop->latitude}, {$stop->longitude})\n";
                        $failed++;
                    }
                } else {
                    echo "❌ [{$stop->id}] {$stop->name} ({$stop->commune}) → AUCUN RÉSULTAT\n";
                    $failed++;
                }
            }
        }
    } else {
        echo "❌ Erreur réseau pour [{$stop->id}] {$stop->name}\n";
        $failed++;
    }
    
    // Nominatim demande max 1 requête/seconde
    sleep(1);
}

echo "\n=== RÉSUMÉ ===\n";
echo "✅ Mis à jour: {$updated}\n";
echo "❌ Non trouvés: {$failed}\n";
echo "Total: " . ($updated + $failed) . " arrêts traités\n";
