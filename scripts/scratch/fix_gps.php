<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\MarketplaceListing;

// Mettre à jour les annonces qui n'ont pas de coordonnées GPS (0,0)
$listings = MarketplaceListing::where('location_latitude', 0)
    ->orWhere('location_latitude', '0.0')
    ->orWhere('location_longitude', 0)
    ->orWhere('location_longitude', '0.0')
    ->orWhereNull('location_latitude')
    ->orWhereNull('location_longitude')
    ->get();

$count = 0;
foreach ($listings as $listing) {
    // Coordonnées de base (Abidjan) avec une légère variation aléatoire pour simuler différents endroits
    $lat = 5.3599 + (rand(-50, 50) / 1000);
    $lng = -4.0083 + (rand(-50, 50) / 1000);
    
    $listing->location_latitude = $lat;
    $listing->location_longitude = $lng;
    $listing->save();
    $count++;
}

echo "Succès : $count annonce(s) mise(s) à jour avec des coordonnées par défaut.\n";
