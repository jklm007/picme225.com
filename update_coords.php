<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$updates = [
    // COCODY
    'Commissariat 8ème' => [5.330, -3.985],
    'Commissariat 12ème' => [5.358, -3.992],
    'Commissariat 30ème' => [5.378, -3.978],
    'Commissariat 18ème' => [5.361, -3.967],
    'Commissariat 22ème' => [5.394, -3.978],
    'Commissariat 35ème' => [5.371, -3.962],
    'Université Félix Houphouët-Boigny' => [5.343, -3.984],
    'Carrefour de la Vie' => [5.338, -3.988],
    'Lycée Classique d\'Abidjan' => [5.340, -3.992],
    'Lycée Sainte Marie' => [5.343, -3.995],
    
    // YOPOUGON
    'CHU de Yopougon' => [5.340, -4.054],
    'Carrefour Siporex' => [5.318, -4.053],
    'Carrefour Sable' => [5.328, -4.062],
    'Place Figayo' => [5.319, -4.083],
    'Commissariat 16ème' => [5.332, -4.053],
    'Commissariat 17ème' => [5.336, -4.088],
    'Commissariat 19ème' => [5.345, -4.092],
    'Pharmacie Bel Air' => [5.342, -4.066],
    'Lycée Scientifique de Yopougon' => [5.337, -4.066],
    'Institut des Aveugles' => [5.325, -4.060],
    'Carrefour Zone Industrielle' => [5.350, -4.050],
    'Maroc, Yopougon' => [5.348, -4.075],
    
    // MARCORY
    'Commissariat 9ème' => [5.295, -3.969],
    'Cap Sud, Marcory' => [5.305, -3.975],
    'Grand Carrefour Marcory' => [5.295, -3.969],
    'Carrefour Solibra' => [5.308, -3.988],
    'Clinique de Marcory' => [5.300, -3.975],
    'Collège Moderne de Marcory' => [5.290, -3.970],
    'Zone 4C' => [5.295, -3.985],
    'Commissariat 26ème' => [5.285, -3.960],
    
    // KOUMASSI
    'Grand Carrefour de Koumassi' => [5.300, -3.948],
    'Commissariat 6ème' => [5.302, -3.946],
    'Commissariat 20ème' => [5.295, -3.945],
    'Hôpital Général de Koumassi' => [5.296, -3.946],
    'Place de l\'Espérance' => [5.302, -3.944],
    'Gare de Koumassi' => [5.299, -3.949],
    'Camp Commando Koumassi' => [5.305, -3.940],
    
    // PLATEAU
    'Préfecture de Police Abidjan' => [5.328, -4.022],
    'Commissariat 1er' => [5.322, -4.020],
    'Cathédrale Saint-Paul' => [5.329, -4.019],
    'Sorbonne' => [5.325, -4.021],
    'Gare Sud SOTRA' => [5.314, -4.015],
    'Hôpital Militaire d\'Abidjan' => [5.330, -4.016],
    'Lycée Technique d\'Abidjan' => [5.330, -3.998],
    'Pharmacie du Plateau' => [5.321, -4.018],
    
    // ABOBO
    'Commissariat 14ème' => [5.412, -4.017],
    'Commissariat 15ème' => [5.420, -4.010],
    'Commissariat 21ème' => [5.435, -4.015],
    'Hôpital Général d\'Abobo' => [5.415, -4.020],
    'Lycée Municipal d\'Abobo' => [5.420, -4.022],
    'Université Nangui Abrogoua' => [5.390, -4.017],
    'Pharmacie Dokui' => [5.385, -4.000],
    
    // ADJAME
    'Forum des Marchés' => [5.352, -4.019],
    'Renault' => [5.360, -4.020],
    'Liberté' => [5.348, -4.015],
    'Commissariat 3ème' => [5.350, -4.015],
    'Commissariat 7ème' => [5.365, -4.020],
    'Hôpital Militaire (HMA)' => [5.362, -4.013],
    'Gare Routière Nord' => [5.364, -4.022],
    'Lycée Moderne d\'Adjamé' => [5.355, -4.016],
    
    // TREICHVILLE
    'CHU de Treichville' => [5.305, -4.004],
    'Commissariat 2ème' => [5.308, -4.005],
    'Commissariat 4ème' => [5.300, -4.000],
    'Gare de Treichville' => [5.311, -4.004],
    'Lycée Moderne de Treichville' => [5.306, -4.008],
    'Pharmacie Arras' => [5.305, -4.003],
    
    // PORT BOUET
    'Commissariat 5ème' => [5.253, -3.957],
    'Hôpital Général de Port-Bouët' => [5.250, -3.955],
    'Lycée Moderne de Port-Bouët' => [5.255, -3.950],
    'Carrefour Akwaba' => [5.260, -3.945],
    'Pharmacie de l\'Aéroport' => [5.262, -3.946],
    
    // ATTECOUBE
    'Mairie d\'Attécoubé' => [5.336, -4.032],
    'Commissariat 10ème' => [5.340, -4.035],
    'Marché d\'Attécoubé' => [5.337, -4.033],
    'Carrefour Sebroko' => [5.330, -4.030],
    'Lycée Municipal d\'Attécoubé' => [5.338, -4.032],
];

$count = 0;
foreach ($updates as $name => $coords) {
    $affected = \DB::table('pdp_stops')
        ->where('name', 'like', "%$name%")
        ->update([
            'latitude' => $coords[0],
            'longitude' => $coords[1]
        ]);
    $count += $affected;
    if ($affected > 0) echo "Updated $name\n";
}
echo "Total updated: $count\n";

