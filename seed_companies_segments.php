<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Helper to avoid duplicates
function createCompany($name) {
    $existing = DB::table('interurban_companies')->where('name', $name)->first();
    if ($existing) return $existing->id;
    return DB::table('interurban_companies')->insertGetId([
        'name' => $name, 'type' => 'BIG', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()
    ]);
}

function createStop($name, $companyId, $commune) {
    $existing = DB::table('pdp_stops')->where('name', $name)->first();
    if ($existing) return $existing->id;
    return DB::table('pdp_stops')->insertGetId([
        'name' => $name, 'interurban_company_id' => $companyId, 'commune' => $commune, 
        'type' => 'INTERURBAN', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now(),
        'latitude' => 5.345, 'longitude' => -4.024 // Placeholder coordinates
    ]);
}

$utb = createCompany('UTB');
$sbta = createCompany('SBTA');

// --- LIGNE UTB : Abidjan - Yamoussoukro ---
$gare_utb_abj = createStop('Gare UTB Adjamé', $utb, 'Adjamé');
$gare_utb_yakro = createStop('Gare UTB Yamoussoukro', $utb, 'Yamoussoukro');

$route_utb_yakro = DB::table('pdp_routes')->insertGetId([
    'name' => 'UTB Express : Abidjan ↔ Yamoussoukro', 
    'interurban_company_id' => $utb, 'type' => 'REGIONAL', 
    'base_price_per_segment' => 4000, 'is_active' => 1, 
    'created_at' => now(), 'updated_at' => now()
]);

DB::table('pdp_route_stops')->insert([
    ['pdp_route_id' => $route_utb_yakro, 'pdp_stop_id' => $gare_utb_abj, 'order' => 1, 'created_at' => now(), 'updated_at' => now()],
    ['pdp_route_id' => $route_utb_yakro, 'pdp_stop_id' => $gare_utb_yakro, 'order' => 2, 'created_at' => now(), 'updated_at' => now()]
]);

DB::table('pdp_route_segments')->insert([
    ['pdp_route_id' => $route_utb_yakro, 'from_stop_id' => $gare_utb_abj, 'to_stop_id' => $gare_utb_yakro, 'order' => 1, 'price' => 4000, 'distance_km' => 240, 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
    ['pdp_route_id' => $route_utb_yakro, 'from_stop_id' => $gare_utb_yakro, 'to_stop_id' => $gare_utb_abj, 'order' => 1, 'price' => 4000, 'distance_km' => 240, 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()]
]);

// --- LIGNE SBTA : Abidjan - San-Pédro ---
$gare_sbta_abj = createStop('Gare SBTA Yopougon', $sbta, 'Yopougon');
$gare_sbta_sp = createStop('Gare SBTA San-Pédro', $sbta, 'San-Pédro');

$route_sbta_sp = DB::table('pdp_routes')->insertGetId([
    'name' => 'SBTA Express : Abidjan ↔ San-Pédro', 
    'interurban_company_id' => $sbta, 'type' => 'REGIONAL', 
    'base_price_per_segment' => 7000, 'is_active' => 1, 
    'created_at' => now(), 'updated_at' => now()
]);

DB::table('pdp_route_stops')->insert([
    ['pdp_route_id' => $route_sbta_sp, 'pdp_stop_id' => $gare_sbta_abj, 'order' => 1, 'created_at' => now(), 'updated_at' => now()],
    ['pdp_route_id' => $route_sbta_sp, 'pdp_stop_id' => $gare_sbta_sp, 'order' => 2, 'created_at' => now(), 'updated_at' => now()]
]);

DB::table('pdp_route_segments')->insert([
    ['pdp_route_id' => $route_sbta_sp, 'from_stop_id' => $gare_sbta_abj, 'to_stop_id' => $gare_sbta_sp, 'order' => 1, 'price' => 7000, 'distance_km' => 350, 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
    ['pdp_route_id' => $route_sbta_sp, 'from_stop_id' => $gare_sbta_sp, 'to_stop_id' => $gare_sbta_abj, 'order' => 1, 'price' => 7000, 'distance_km' => 350, 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()]
]);

echo "Exemples avec compagnies, gares et prix réels générés !\n";
