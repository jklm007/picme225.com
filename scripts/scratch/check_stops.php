<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$lat = 5.382643;
$lng = -3.9634618;

$stops = \App\PdpStop::selectRaw('name, latitude, longitude, (6371000 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance', [$lat, $lng, $lat])
    ->where('is_active', 1)
    ->orderBy('distance')
    ->limit(5)
    ->get();

echo "Closest stops to ($lat, $lng):\n";
foreach ($stops as $s) {
    echo "- " . $s->name . " (" . round($s->distance, 2) . "m)\n";
}
echo "\nTotal PDP stops in DB: " . \App\PdpStop::count() . "\n";
