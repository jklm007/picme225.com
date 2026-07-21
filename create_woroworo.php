<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\ServiceType;
use App\Service;

try {
    DB::beginTransaction();

    // Trouver la catégorie "Taxi" (ou la première dispo)
    $taxiCategory = Service::where('id', 1)->first() ?: Service::where('name', 'like', '%Taxi%')->first() ?: Service::first();

    if (!$taxiCategory) {
        throw new \Exception("La catégorie principale n'existe pas.");
    }

    // Créer le ServiceType
    $serviceType = ServiceType::create([
        'name' => 'Woroworo',
        'provider_name' => 'Chauffeur Woroworo',
        'capacity' => 4,
        'fixed' => 500, // prix de base très abordable pour un woroworo
        'price' => 100,
        'minute' => 0,
        'hour' => 0,
        'distance' => 1,
        'calculator' => 'DISTANCE',
        'description' => "Taxi communal partagé (Woroworo).",
        'status' => 1,
        'type' => 'standard',
        'is_communal' => 1,      // Spécifié comme "communal"
        'is_taxable' => 1,
        'image' => 'https://via.placeholder.com/150', // Image par défaut pour éviter les plantages de l'app mobile
        // Pour qu'il s'affiche dans les onglets de base si filtré par la variante :
        'allowed_variants' => ["prive", "partage"], 
    ]);

    // Lier le ServiceType à la catégorie Taxi via la table pivot
    DB::table('service_service_type')->insert([
        'service_id' => $taxiCategory->id,
        'service_type_id' => $serviceType->id,
        'name' => $serviceType->name,
        'fixed' => $serviceType->fixed,
        'price' => $serviceType->price,
        'minute' => $serviceType->minute,
        'hour' => $serviceType->hour,
        'distance' => $serviceType->distance,
        'calculator' => $serviceType->calculator,
        'description' => $serviceType->description,
        'status' => $serviceType->status,
        'ambulance' => 0,
    ]);

    DB::commit();
    echo "Succes: Le service 'Woroworo' a ete cree (Communal active) et assigne a la categorie '{$taxiCategory->name}' ! (ID: " . $serviceType->id . ")\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "Erreur: " . $e->getMessage() . "\n";
}
