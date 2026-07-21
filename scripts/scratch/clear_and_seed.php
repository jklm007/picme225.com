<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Nettoyage de la base de données...\n";

try {
    // Désactiver les contraintes de clés étrangères
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');

    echo "- Suppression des ServiceTypes...\n";
    DB::table('service_types')->truncate();

    echo "- Suppression des PdpRoutes, Stops et Segments...\n";
    DB::table('pdp_route_segments')->truncate();
    DB::table('pdp_stops')->truncate();
    DB::table('pdp_routes')->truncate();

    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    echo "Base de données nettoyée avec succès.\n";
} catch (\Exception $e) {
    echo "Erreur lors du nettoyage : " . $e->getMessage() . "\n";
    exit(1);
}

echo "Lancement des seeders...\n";

// Lancer les seeders via Artisan
try {
    Artisan::call('db:seed', ['--class' => 'PdpRoutesSeeder']);
    echo "PdpRoutesSeeder terminé.\n";
    
    Artisan::call('db:seed', ['--class' => 'OutstationRoutesSeeder']);
    echo "OutstationRoutesSeeder terminé.\n";
} catch (\Exception $e) {
    echo "Erreur lors du seeding : " . $e->getMessage() . "\n";
    exit(1);
}

echo "Opération terminée avec succès !\n";
