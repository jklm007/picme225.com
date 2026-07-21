<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\ServiceType;
use App\Service;

try {
    DB::beginTransaction();

    // 1. Créer le Taxi
    $taxiCategory = Service::where('id', 1)->first() ?: Service::where('name', 'like', '%Taxi%')->first() ?: Service::first();
    
    $taxi = ServiceType::create([
        'name' => 'Taxi VTC',
        'provider_name' => 'Chauffeur VTC',
        'capacity' => 4,
        'fixed' => 1000,
        'price' => 200,
        'minute' => 10,
        'hour' => 0,
        'distance' => 5,
        'calculator' => 'DISTANCE',
        'description' => "Service de taxi privé.",
        'status' => 1,
        'type' => 'standard',
        'allowed_variants' => ["prive", "partage"],
        'is_taxable' => 1,
    ]);

    if ($taxiCategory) {
        DB::table('service_service_type')->insert([
            'service_id' => $taxiCategory->id,
            'service_type_id' => $taxi->id,
            'name' => $taxi->name,
            'fixed' => $taxi->fixed,
            'price' => $taxi->price,
            'minute' => $taxi->minute,
            'hour' => $taxi->hour,
            'distance' => $taxi->distance,
            'calculator' => $taxi->calculator,
            'description' => $taxi->description,
            'status' => $taxi->status,
            'ambulance' => 0,
        ]);
        echo "Taxi VTC cree et assigne a la categorie '{$taxiCategory->name}'\n";
    }

    // 2. Créer la Location
    $locationCategory = Service::where('name', 'Location')->first();
    
    $location = ServiceType::create([
        'name' => 'Berline (Location)',
        'provider_name' => 'Location Berline',
        'capacity' => 4,
        'fixed' => 0,
        'price' => 0,
        'minute' => 0,
        'hour' => 5000,
        'distance' => 1,
        'calculator' => 'HOUR',
        'description' => "Location de véhicule à l'heure.",
        'status' => 1,
        'type' => 'rental',
        'rental_amount' => 50000,
        'allowed_variants' => ["avec_chauffeur", "sans_chauffeur"],
        'is_taxable' => 1,
    ]);

    if ($locationCategory) {
        DB::table('service_service_type')->insert([
            'service_id' => $locationCategory->id,
            'service_type_id' => $location->id,
            'name' => $location->name,
            'fixed' => $location->fixed,
            'price' => $location->price,
            'minute' => $location->minute,
            'hour' => $location->hour,
            'distance' => $location->distance,
            'calculator' => $location->calculator,
            'description' => $location->description,
            'status' => $location->status,
            'ambulance' => 0,
        ]);
        echo "Location Berline creee et assignee a la categorie 'Location'\n";
    }

    DB::commit();
    echo "Succes !\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "Erreur: " . $e->getMessage() . "\n";
}
