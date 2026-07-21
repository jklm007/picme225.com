<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\PdpStop;
use Illuminate\Support\Facades\Http;

$stops = PdpStop::all();
echo "Démarrage de la mise à jour de " . count($stops) . " arrêts via Photon...\n";

// Bounding box approximative de la Côte d'Ivoire pour limiter les résultats
$bbox = "-9.0,4.0,-2.0,11.0";
// Centre d'Abidjan pour biaiser la recherche locale (spatial priorization)
$latAbidjan = 5.3453;
$lonAbidjan = -4.0244;

$updatedCount = 0;
$failedCount = 0;

foreach ($stops as $stop) {
    // Construction de la requête : On ajoute Abidjan/Côte d'Ivoire pour plus de précision si ce n'est pas explicite
    $queryName = trim($stop->name);
    
    // Si c'est une ville de l'intérieur, on ne force pas Abidjan
    $searchQuery = $queryName;
    if (stripos($queryName, 'Abidjan') === false && stripos($queryName, 'Yamoussoukro') === false && stripos($queryName, 'Bouaké') === false && stripos($queryName, 'San-Pédro') === false && stripos($queryName, 'Daloa') === false && stripos($queryName, 'Korhogo') === false) {
        $searchQuery .= " Abidjan Côte d'Ivoire";
    }

    echo "Recherche de : " . $searchQuery . "\n";

    // Appel à l'API publique Photon (Komoot)
    try {
        $response = Http::timeout(10)->get('https://photon.komoot.io/api/', [
            'q' => $searchQuery,
            'limit' => 1,
            'lat' => $latAbidjan,
            'lon' => $lonAbidjan,
            'bbox' => $bbox
        ]);

        if ($response->successful()) {
            $data = $response->json();
            if (isset($data['features']) && count($data['features']) > 0) {
                $feature = $data['features'][0];
                $geometry = $feature['geometry']['coordinates']; // [longitude, latitude]
                
                $newLon = $geometry[0];
                $newLat = $geometry[1];

                $stop->latitude = $newLat;
                $stop->longitude = $newLon;
                $stop->save();

                echo "  -> SUCCÈS : [" . $newLat . ", " . $newLon . "] (" . ($feature['properties']['name'] ?? 'Nom inconnu') . ")\n";
                $updatedCount++;
            } else {
                echo "  -> ÉCHEC : Aucun résultat trouvé sur OpenStreetMap.\n";
                $failedCount++;
            }
        } else {
            echo "  -> ERREUR HTTP : " . $response->status() . "\n";
            $failedCount++;
        }
    } catch (\Exception $e) {
        echo "  -> ERREUR EXCEPTION : " . $e->getMessage() . "\n";
        $failedCount++;
    }

    // Délai de 1 seconde pour respecter la limite de l'API publique Photon et éviter d'être banni
    usleep(1000000);
}

echo "Mise à jour terminée !\n";
echo "Arrêts mis à jour : $updatedCount\n";
echo "Arrêts introuvables : $failedCount\n";
