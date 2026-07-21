<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Toutes les catégories
echo "=== SERVICES (catégories) ===\n";
$services = DB::table('services')->get();
foreach ($services as $s) {
    echo "  ID {$s->id} : {$s->name}\n";
}

// Pivot service_service_type
echo "\n=== TABLE PIVOT service_service_type ===\n";
$pivot = DB::table('service_service_type')
    ->join('service_types', 'service_service_type.service_type_id', '=', 'service_types.id')
    ->join('services', 'service_service_type.service_id', '=', 'services.id')
    ->select('services.id as cat_id', 'services.name as category', 'service_types.id as st_id', 'service_types.name as vehicle', 'service_types.type', 'service_types.allowed_variants')
    ->orderBy('services.id')
    ->get();

$current = null;
foreach ($pivot as $row) {
    if ($current !== $row->category) {
        $current = $row->category;
        echo "\n  [{$row->cat_id}] {$row->category}:\n";
    }
    $variants = $row->allowed_variants;
    echo "    - ST#{$row->st_id} {$row->vehicle} (type:{$row->type}) variants:{$variants}\n";
}

// Types sans pivot
echo "\n=== SERVICE TYPES SANS PIVOT (orphelins) ===\n";
$orphans = DB::table('service_types')
    ->leftJoin('service_service_type', 'service_types.id', '=', 'service_service_type.service_type_id')
    ->whereNull('service_service_type.service_type_id')
    ->select('service_types.id', 'service_types.name', 'service_types.type')
    ->get();
foreach ($orphans as $o) {
    echo "  ST#{$o->id} {$o->name} (type:{$o->type}) → NON ATTACHÉ !\n";
}
