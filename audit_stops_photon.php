<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== TEST PHOTON SUR TOUS LES ARRETS (avec résultat vide) ===\n\n";

$stops = DB::table('pdp_stops')
    ->whereNotNull('name')
    ->orderBy('commune')
    ->get(['id','name','latitude','longitude','commune']);

$noResult = [];
$wrongCountry = [];
$ok = 0;

foreach ($stops as $stop) {
    $query = $stop->name . ' Abidjan Côte d\'Ivoire';
    $url = 'https://photon.komoot.io/api/?q=' . urlencode($query) . '&lat=5.35&lon=-4.00&location_bias_scale=0.6&limit=1';
    $r = @file_get_contents($url);
    
    if ($r) {
        $d = json_decode($r, true);
        $features = $d['features'] ?? [];
        if (empty($features)) {
            $noResult[] = $stop;
            echo "❌ Aucun résultat Photon: [{$stop->id}] {$stop->name} ({$stop->commune})\n";
        } else {
            $country = $features[0]['properties']['country'] ?? '';
            $c = $features[0]['geometry']['coordinates'];
            $lat = $c[1];
            $lon = $c[0];
            
            if (!empty($country) && !in_array($country, ['Côte d\'Ivoire', 'Ivory Coast'])) {
                $wrongCountry[] = $stop;
                echo "⚠️ Mauvais pays ({$country}): [{$stop->id}] {$stop->name} ({$stop->commune}) → lat:{$lat} lon:{$lon}\n";
            } else {
                $ok++;
            }
        }
    }
    sleep(1);
}

echo "\n=== RÉSUMÉ ===\n";
echo "✅ OK (Photon trouve): {$ok}\n";
echo "❌ Sans résultat: " . count($noResult) . "\n";
echo "⚠️ Mauvais pays: " . count($wrongCountry) . "\n";

echo "\nArrêts sans résultat à corriger manuellement:\n";
foreach ($noResult as $s) {
    echo "  [{$s->id}] {$s->name} | actuel: {$s->latitude}, {$s->longitude} | {$s->commune}\n";
}
