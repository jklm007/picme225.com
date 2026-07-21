<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

use App\PdpStop;
use Illuminate\Support\Facades\Http;

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== RECALIBRAGE DES COORDONNÉES VIA PHOTON ===\n\n";

$stops = PdpStop::all();
$count = 0;
$updated = 0;

foreach ($stops as $stop) {
    $count++;
    $searchQuery = $stop->name . ", " . ($stop->commune ?: 'Abidjan') . ", Côte d'Ivoire";
    echo "[$count/" . $stops->count() . "] Recherche pour : $searchQuery ... ";

    try {
        $response = Http::timeout(10)->get('https://photon.komoot.io/api/', [
            'q' => $searchQuery,
            'limit' => 1
        ]);

        if ($response->successful()) {
            $data = $response->json();
            if (!empty($data['features'])) {
                $coords = $data['features'][0]['geometry']['coordinates']; // [lng, lat]
                $newLng = $coords[0];
                $newLat = $coords[1];

                // Calculer la différence pour info
                $diffLat = abs($stop->latitude - $newLat);
                $diffLng = abs($stop->longitude - $newLng);

                if ($diffLat > 0.0001 || $diffLng > 0.0001) {
                    $stop->latitude = $newLat;
                    $stop->longitude = $newLng;
                    $stop->save();
                    echo "✅ MIS À JOUR (Nouveau: $newLat, $newLng)\n";
                    $updated++;
                } else {
                    echo "🆗 DÉJÀ PRÉCIS\n";
                }
            } else {
                echo "⚠️ NON TROUVÉ SUR PHOTON\n";
            }
        } else {
            echo "❌ ERREUR API\n";
        }
    } catch (\Exception $e) {
        echo "❌ ERREUR : " . $e->getMessage() . "\n";
    }

    // Petite pause pour ne pas saturer l'API
    usleep(200000); 
}

echo "\nTerminé ! $updated arrêts ont été recalibrés sur $count.\n";
