<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Colonnes de service_types ===\n";
$cols = DB::select('SHOW COLUMNS FROM service_types');
foreach ($cols as $c) {
    echo "  {$c->Field} ({$c->Type})\n";
}

echo "\n=== Service Types liés à Location ===\n";
$types = DB::select("
    SELECT st.*
    FROM service_types st
    JOIN service_service_type sst ON sst.service_type_id = st.id
    JOIN services s ON s.id = sst.service_id
    WHERE s.name = 'Location'
    ORDER BY st.id
");

foreach ($types as $t) {
    $arr = (array)$t;
    echo "\n  --- ID: {$t->id} | Nom: {$t->name} | Type: {$t->type} ---\n";
    // Show image-related fields
    foreach (['image', 'icon', 'photo', 'url', 'avatar', 'picture', 'img'] as $kw) {
        foreach ($arr as $k => $v) {
            if (stripos($k, $kw) !== false) {
                echo "  {$k} = " . (empty($v) ? "(VIDE)" : $v) . "\n";
            }
        }
    }
}

echo "\n=== Vérification getImageUrl() dans ServiceType.php (Android) ===\n";
echo "La méthode getImageUrl() récupère probablement le champ 'image' ou 'icon'\n";
echo "Si ce champ est vide → le carousel SKIP ce véhicule\n";
