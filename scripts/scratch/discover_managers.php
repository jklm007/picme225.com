<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Analyse des roles et gestionnaires ('Manager') ===\n\n";

// 1. Lister les types d'utilisateurs uniques
echo "1. Types d'utilisateurs dans la table 'users':\n";
$userTypes = DB::table('users')->select('user_type')->distinct()->pluck('user_type');
foreach ($userTypes as $type) {
    $count = DB::table('users')->where('user_type', $type)->count();
    echo "- Role: " . ($type ?: 'NULL (Standard User)') . " | Nombre: $count\n";
}

// 2. Chercher des tables de gestion
echo "\n2. Tables liees a la gestion (Manager/Fleet/Branch):\n";
$tables = DB::select('SHOW TABLES');
$foundTables = [];
foreach ($tables as $table) {
    $tableName = array_values((array) $table)[0];
    if (stripos($tableName, 'manager') !== false || stripos($tableName, 'fleet') !== false || stripos($tableName, 'agent') !== false || stripos($tableName, 'branch') !== false) {
        $foundTables[] = $tableName;
        echo "- Table: $tableName\n";
    }
}

// 3. Verifier la structure de 'fleets' (Proprietaires de compagnies)
if (Schema::hasTable('fleets')) {
    echo "\n3. Structure de la table 'fleets' (Proprietaires):\n";
    $columns = Schema::getColumnListing('fleets');
    echo "Colonnes: " . implode(', ', $columns) . "\n";
}

// 4. Verifier si une table 'account_managers' ou similaire existe
foreach (['account_managers', 'branch_managers', 'station_agents'] as $t) {
    if (Schema::hasTable($t)) {
        echo "\nStructure de '$t':\n";
        echo "Colonnes: " . implode(', ', Schema::getColumnListing($t)) . "\n";
    }
}
