<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$updates = [
    // Route C1
    "CHU de Cocody" => ["lat" => 5.343169, "lng" => -3.984252],
    "Campus 2000" => ["lat" => 5.341100, "lng" => -3.987100],
    "Cité Mermoz" => ["lat" => 5.338100, "lng" => -3.989100],
    "INSAAC" => ["lat" => 5.343100, "lng" => -3.992100],
    "Carrefour Duncan" => ["lat" => 5.352422, "lng" => -3.990263],
    "Riviera 2" => ["lat" => 5.355711, "lng" => -3.971212],

    // Route C2
    "Carrefour Faya" => ["lat" => 5.367851, "lng" => -3.953683],
    "Jules Verne" => ["lat" => 5.365100, "lng" => -3.955100],
    "Carrefour Palmeraie" => ["lat" => 5.360982, "lng" => -3.963214],
    "Riviera 3 (9 Kilos)" => ["lat" => 5.355104, "lng" => -3.967812],
    "Cap Nord" => ["lat" => 5.351239, "lng" => -3.974011],

    // Route Y1
    "Terminus 27 (Niangon)" => ["lat" => 5.314200, "lng" => -4.095100],
    "Académie" => ["lat" => 5.318200, "lng" => -4.088100],
    "Maroc (Antenne)" => ["lat" => 5.326200, "lng" => -4.078100],
    "Sable" => ["lat" => 5.328320, "lng" => -4.062015],
    "Siporex" => ["lat" => 5.334125, "lng" => -4.053186],

    // Route Y2
    "Zone Industrielle" => ["lat" => 5.348200, "lng" => -4.048100],
    "Carrefour MACA" => ["lat" => 5.338200, "lng" => -4.085100],
    "BAE" => ["lat" => 5.335200, "lng" => -4.090100],
    "Toits Rouges" => ["lat" => 5.332200, "lng" => -4.095100],
    "Port-Bouët 2" => ["lat" => 5.330200, "lng" => -4.100100],

    // Route A1
    "Abobo Baoulé" => ["lat" => 5.415200, "lng" => -4.015100],
    "Carrefour Samaké" => ["lat" => 5.405200, "lng" => -4.020100],
    "Mairie d'Abobo" => ["lat" => 5.416600, "lng" => -4.016600],
    "Gare d'Abobo" => ["lat" => 5.414200, "lng" => -4.021100],

    // Route M1
    "INJS" => ["lat" => 5.298150, "lng" => -3.983944],
    "Sicogi" => ["lat" => 5.305100, "lng" => -3.988200],
    "Marché de Marcory" => ["lat" => 5.303100, "lng" => -3.991200],
    "Carrefour Solibra" => ["lat" => 5.308696, "lng" => -3.988352],
    "Zone 4 (Rue PMC)" => ["lat" => 5.295100, "lng" => -3.985100],
];

foreach ($updates as $name => $coords) {
    // Il y a peut-être des espaces, on va faire un like
    $stop = \App\Models\PdpStop::where('name', 'LIKE', '%' . $name . '%')->first();
    if ($stop) {
        $stop->latitude = $coords['lat'];
        $stop->longitude = $coords['lng'];
        $stop->save();
        echo "Updated $name to " . $coords['lat'] . ", " . $coords['lng'] . "\n";
    } else {
        echo "NOT FOUND: $name\n";
    }
}
