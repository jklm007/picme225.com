<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$updates = [
    163 => ["lat" => 5.318200, "lng" => -4.088100], // Académie
    171 => ["lat" => 5.330200, "lng" => -4.100100], // Port-Bouët 2
    177 => ["lat" => 5.310000, "lng" => -3.995000], // Marché de Marcory
    149 => ["lat" => 5.250000, "lng" => -3.950000], // Port-Bouët Phare (restauré)
    130 => ["lat" => 5.252000, "lng" => -3.951000], // Phare de Port-Bouët (restauré)
];

// Restore Grand Marché Koumassi if it exists and was changed
$marches = \App\Models\PdpStop::where('name', 'LIKE', '%March%')->get();
foreach ($marches as $m) {
    if (strpos($m->name, 'Koumassi') !== false) {
        $m->latitude = 5.295000;
        $m->longitude = -3.948000;
        $m->save();
        echo "Restored " . $m->name . "\n";
    }
}

foreach ($updates as $id => $coords) {
    $stop = \App\Models\PdpStop::find($id);
    if ($stop) {
        $stop->latitude = $coords['lat'];
        $stop->longitude = $coords['lng'];
        $stop->save();
        echo "Updated {$stop->name} to " . $coords['lat'] . ", " . $coords['lng'] . "\n";
    }
}
