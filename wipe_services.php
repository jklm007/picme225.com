<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    
    // Vider la table principale
    DB::table('service_types')->truncate();
    
    // Vider les tables de liaison
    DB::table('service_service_type')->truncate();
    DB::table('service_type_rentals')->truncate();
    
    // Optionnel : Vider les services assignés aux chauffeurs pour éviter des bugs
    if (Schema::hasTable('provider_services')) {
        DB::table('provider_services')->truncate();
    }
    
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    echo "Succes: Tous les Service Types ont ete supprimes !";
} catch (\Exception $e) {
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    echo "Erreur: " . $e->getMessage();
}
