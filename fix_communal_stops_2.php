<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$updates = [
    "Mermoz" => ["lat" => 5.338100, "lng" => -3.989100],
    "cadémie" => ["lat" => 5.318200, "lng" => -4.088100],
    "Port-Bou" => ["lat" => 5.330200, "lng" => -4.100100],
    "Baoul" => ["lat" => 5.415200, "lng" => -4.015100],
    "Samak" => ["lat" => 5.405200, "lng" => -4.020100],
    "March" => ["lat" => 5.310000, "lng" => -3.995000],
];

foreach ($updates as $name => $coords) {
    $stop = \App\Models\PdpStop::where('name', 'LIKE', '%' . $name . '%')->first();
    if ($stop) {
        $stop->latitude = $coords['lat'];
        $stop->longitude = $coords['lng'];
        $stop->save();
        echo "Updated {$stop->name} to " . $coords['lat'] . ", " . $coords['lng'] . "\n";
    } else {
        echo "NOT FOUND: $name\n";
    }
}
