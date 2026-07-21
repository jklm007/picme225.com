<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Fetch all services
$categories = DB::table('services')->get();
echo "=== CATEGORIES ===\n";
foreach ($categories as $c) {
    echo "ID: {$c->id} | Name: {$c->name}\n";
}

// Fetch service types mapped to Location (3) and Voyage (4)
$services = DB::table('service_types')
    ->join('service_service_type', 'service_types.id', '=', 'service_service_type.service_type_id')
    ->select('service_types.id', 'service_types.name', 'service_service_type.service_id', 'service_types.commission_percentage')
    ->get();

echo "\n=== TABLEAU DES COMMISSIONS LOCATION ET VOYAGE ===\n";
foreach ($services as $s) {
    if ($s->service_id == 3 || $s->service_id == 4) {
        $cat = $s->service_id == 3 ? 'LOCATION' : 'VOYAGE';
        echo "[{$cat}] {$s->name} : {$s->commission_percentage}% de commission\n";
    }
}
