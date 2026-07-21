<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

use Illuminate\Support\Facades\DB;

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Contenu de la table service_service_type ===\n\n";

$rows = DB::table('service_service_type')->get();

foreach ($rows as $row) {
    echo "Service ID: {$row->service_id} | Type ID: {$row->service_type_id} | Ambulance: {$row->ambulance}\n";
}

echo "\n=== Vérification des types existants ===\n";
$typeIds = DB::table('service_types')->pluck('id')->toArray();
echo "Type IDs valides: " . implode(', ', $typeIds) . "\n";

foreach ($rows as $row) {
    if (!in_array($row->service_type_id, $typeIds)) {
        echo "!!! Ligne orpheline detectee: Type ID {$row->service_type_id} n'existe plus !\n";
    }
}
