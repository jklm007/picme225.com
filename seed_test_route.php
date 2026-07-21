<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PdpRoute;
use App\Models\PdpStop;
use App\Models\PdpRouteSegment;

DB::beginTransaction();

try {
    // 1. Créer la ligne interurbaine
    $route = PdpRoute::create([
        'name' => 'Abidjan ↔ Bouaké',
        'type' => 'INTERURBAN',
        'status' => 'APPROVED',
        'description' => 'Ligne express interurbaine (Test)',
        'is_active' => true,
        'base_price_per_segment' => 5000,
        'created_by_user_id' => 1
    ]);

    // 2. Créer les arrêts
    $stop1 = PdpStop::create([
        'name' => 'Gare Nord Adjamé',
        'latitude' => 5.3653,
        'longitude' => -4.0187,
        'commune' => 'Adjamé',
        'address' => 'Gare Nord, Adjamé, Abidjan',
        'is_active' => true
    ]);

    $stop2 = PdpStop::create([
        'name' => 'Gare de Yamoussoukro',
        'latitude' => 6.8167,
        'longitude' => -5.2833,
        'commune' => 'Yamoussoukro',
        'address' => 'Gare Centrale, Yamoussoukro',
        'is_active' => true
    ]);

    $stop3 = PdpStop::create([
        'name' => 'Gare de Bouaké',
        'latitude' => 7.6938,
        'longitude' => -5.0303,
        'commune' => 'Bouaké',
        'address' => 'Gare Principale, Bouaké',
        'is_active' => true
    ]);

    // 3. Lier les arrêts à la route (table pivot pdp_route_stops)
    $route->stops()->attach($stop1->id, ['order' => 1, 'price' => 0]);
    $route->stops()->attach($stop2->id, ['order' => 2, 'price' => 5000]);
    $route->stops()->attach($stop3->id, ['order' => 3, 'price' => 8000]);

    // 4. Créer les segments pour que la distance et le prix total soient calculés
    PdpRouteSegment::create([
        'pdp_route_id' => $route->id,
        'from_stop_id' => $stop1->id,
        'to_stop_id' => $stop2->id,
        'distance_km' => 240,
        'estimated_time_mins' => 150,
        'price' => 5000,
        'order' => 1,
        'is_active' => true
    ]);

    PdpRouteSegment::create([
        'pdp_route_id' => $route->id,
        'from_stop_id' => $stop2->id,
        'to_stop_id' => $stop3->id,
        'distance_km' => 110,
        'estimated_time_mins' => 90,
        'price' => 3000,
        'order' => 2,
        'is_active' => true
    ]);

    DB::commit();
    echo "Ligne Abidjan ↔ Bouaké créée avec succès (ID: " . $route->id . ")\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "Erreur: " . $e->getMessage() . "\n";
}
