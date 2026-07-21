<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\ServiceType;
use App\Service;

try {
    DB::beginTransaction();

    // Trouver la catégorie "Urgence"
    $urgenceCategory = Service::where('name', 'Urgence')->first();
    
    if (!$urgenceCategory) {
        throw new \Exception("La catégorie principale 'Urgence' n'existe pas dans la table 'services'.");
    }

    // Créer le ServiceType
    $serviceType = ServiceType::create([
        'name' => 'Ambulance',
        'provider_name' => 'Ambulance Médicalisée',
        'capacity' => 2,
        'fixed' => 5000,
        'price' => 500,
        'minute' => 0,
        'hour' => 0,
        'distance' => 1,
        'calculator' => 'DISTANCE',
        'description' => "Service de transport médical d'urgence.",
        'ambulance' => 1,
        'status' => 1,
        'type' => 'standard',
        'allowed_variants' => ["ambulance"],
        'is_taxable' => 1,
    ]);

    // Lier le ServiceType à la catégorie Urgence via la table pivot
    DB::table('service_service_type')->insert([
        'service_id' => $urgenceCategory->id,
        'service_type_id' => $serviceType->id,
        'name' => $serviceType->name,  // <-- AJOUTE ICI
        'fixed' => $serviceType->fixed,
        'price' => $serviceType->price,
        'minute' => $serviceType->minute,
        'hour' => $serviceType->hour,
        'distance' => $serviceType->distance,
        'calculator' => $serviceType->calculator,
        'description' => $serviceType->description,
        'status' => $serviceType->status,
        'ambulance' => $serviceType->ambulance,
    ]);

    DB::commit();
    echo "Succes: Le service 'Ambulance' a ete cree et assigne a la categorie 'Urgence' ! (ID: " . $serviceType->id . ")\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "Erreur: " . $e->getMessage() . "\n";
}
