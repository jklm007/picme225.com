<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    
    echo "Nettoyage des tables...\n";
    
    // Liste des tables à vider
    $tables = [
        'service_service_type',
        'service_type_rentals',
        'service_types',
        'services',
        'provider_services', // Optionnel mais recommandé si on change les types
    ];

    foreach ($tables as $table) {
        if (Schema::hasTable($table)) {
            DB::table($table)->truncate();
            echo "- Table '$table' vidée.\n";
        }
    }

    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    echo "\n[SUCCÈS] Toutes les catégories et types de services ont été supprimés.\n";
    echo "Vous pouvez maintenant les recréer manuellement.\n";

} catch (Exception $e) {
    echo "\n[ERREUR] " . $e->getMessage() . "\n";
}
