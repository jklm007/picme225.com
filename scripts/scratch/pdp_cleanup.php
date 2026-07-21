<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

use App\PdpRoute;
use App\PdpRouteSegment;

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Mise à jour finale des données PDP...\n";

// 1. Remplir les prix de base manquant sur les routes
PdpRoute::whereNull('base_price_per_segment')
    ->orWhere('base_price_per_segment', 0)
    ->update(['base_price_per_segment' => 200]);

PdpRoute::whereNull('detour_price_per_km')
    ->orWhere('detour_price_per_km', 0)
    ->update(['detour_price_per_km' => 300]);

PdpRoute::whereNull('description')
    ->update(['description' => 'Service de transport à prix fixe aux arrêts.']);

// 2. S'assurer qu'aucun segment n'a de distance null ou 0
$segments = App\PdpRouteSegment::all();
foreach ($segments as $s) {
    if (is_null($s->distance_km) || $s->distance_km <= 0) {
        $s->distance_km = 1.0; 
        $s->save();
    }
}

// 3. Nettoyage des arrêts
App\PdpStop::whereNull('type')->update(['type' => 'arret']);
App\PdpStop::where('order', 1)->update(['type' => 'gare']); // Le premier est une gare

echo "Nettoyage terminé avec succès.\n";
