<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== PIVOT TABLE: service_service_type complet ===\n";
$pivots = DB::select("
    SELECT sst.service_id, s.name as service_name,
           sst.service_type_id, st.name as type_name, st.type as type_field,
           st.allowed_variants
    FROM service_service_type sst
    JOIN services s ON s.id = sst.service_id
    JOIN service_types st ON st.id = sst.service_type_id
    ORDER BY s.name, st.id
");

$currentService = '';
foreach ($pivots as $p) {
    if ($p->service_name !== $currentService) {
        echo "\n  [Service: {$p->service_name} (ID: {$p->service_id})]\n";
        $currentService = $p->service_name;
    }
    $variants = $p->allowed_variants ?? '[]';
    echo "    → ServiceType ID:{$p->service_type_id} | {$p->type_name} (type={$p->type_field}) | variants={$variants}\n";
}

echo "\n\n=== PROBLÈME DÉTECTÉ ===\n";
// Service types linked to Location but NOT of type 'rental'
$wrong = DB::select("
    SELECT st.id, st.name, st.type, st.allowed_variants
    FROM service_types st
    JOIN service_service_type sst ON sst.service_type_id = st.id
    JOIN services s ON s.id = sst.service_id
    WHERE s.name = 'Location' AND st.type != 'rental'
");

if (empty($wrong)) {
    echo "Aucun problème de pivot détecté.\n";
} else {
    echo "Ces service_types sont liés à 'Location' mais ont type != 'rental':\n";
    foreach ($wrong as $w) {
        echo "  ⚠️  ID:{$w->id} | {$w->name} | type={$w->type} | variants={$w->allowed_variants}\n";
    }
    echo "\nCES SERVICE TYPES TAXI APPARAISSENT DANS LOCATION PAR ERREUR.\n";
    echo "Ils seront filtrés côté Android (variants ne contiennent pas 'avec_chauffeur')\n";
    echo "MAIS le backend les renvoie quand même → N+1 queries inutiles + risque futur.\n";
}

echo "\n=== SOLUTION ===\n";
echo "Supprimer ces entrées du pivot OU filtrer par type='rental' côté backend.\n";
