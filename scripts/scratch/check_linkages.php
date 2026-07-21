<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

use App\ServiceType;
use App\Service;
use Illuminate\Support\Facades\DB;

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Vérification des Maillages (Service ↔ ServiceType) ===\n\n";

$linkages = DB::table('service_service_type')
    ->join('services', 'service_service_type.service_id', '=', 'services.id')
    ->join('service_types', 'service_service_type.service_type_id', '=', 'service_types.id')
    ->select('services.name as service_name', 'service_types.name as type_name', 'service_service_type.service_id', 'service_service_type.service_type_id')
    ->get();

if ($linkages->count() === 0) {
    echo "Aucun maillage trouvé dans service_service_type.\n";
} else {
    foreach ($linkages as $link) {
        echo "Service: {$link->service_name} (ID: {$link->service_id}) ↔ Type: {$link->type_name} (ID: {$link->service_type_id})\n";
    }
}

echo "\n=== Liste des Services Principaux ===\n";
$services = Service::all();
foreach ($services as $s) {
    echo "- ID: {$s->id} | Name: {$s->name}\n";
}
