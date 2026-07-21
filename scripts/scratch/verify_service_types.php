<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

use App\ServiceType;
use Illuminate\Support\Facades\DB;

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Vérification des ServiceTypes ===\n\n";

// 1. Lister tous les ServiceTypes
$allTypes = ServiceType::all();
echo "Total ServiceTypes: " . $allTypes->count() . "\n\n";

foreach ($allTypes as $type) {
    echo "ID: {$type->id} | Name: {$type->name} | Ambulance: {$type->ambulance} | Status: {$type->status}\n";

    // Vérifier les services liés via la table pivot
    $services = DB::table('service_service_type')
        ->join('services', 'service_service_type.service_id', '=', 'services.id')
        ->where('service_service_type.service_type_id', $type->id)
        ->select('services.name as service_name', 'service_service_type.ambulance as pivot_ambulance')
        ->get();

    if ($services->count() > 0) {
        foreach ($services as $service) {
            echo "  → Lié au service: {$service->service_name} (pivot ambulance: {$service->pivot_ambulance})\n";
        }
    } else {
        echo "  → Aucun service lié\n";
    }
    echo "\n";
}

echo "\n=== ServiceTypes avec ambulance != 1 (ce que rental_service retourne) ===\n\n";
$nonAmbulance = ServiceType::where('ambulance', '!=', '1')->get();
foreach ($nonAmbulance as $type) {
    echo "- {$type->name} (ambulance = {$type->ambulance})\n";
}

echo "\n=== ServiceTypes avec ambulance = 1 (exclus de rental_service) ===\n\n";
$ambulanceTypes = ServiceType::where('ambulance', '=', '1')->get();
foreach ($ambulanceTypes as $type) {
    echo "- {$type->name}\n";
}
