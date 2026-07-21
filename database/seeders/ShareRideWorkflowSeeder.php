<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShareRideWorkflowSeeder extends Seeder
{
    public function run(): void
    {
        if (\DB::getDriverName() === 'pgsql') { \DB::statement("SET session_replication_role = 'replica';"); } else { \DB::statement('SET FOREIGN_KEY_CHECKS=0;'); }

        // 1. Trouver ou créer la catégorie de service "Partage"
        $partageService = Service::where('name', 'Partage')->first();
        if (!$partageService) {
            $partageService = Service::create([
                'name' => 'Partage',
                'image' => 'service/shared_ride.jpg'
            ]);
        }

        // 2. Définir les 3 types de véhicules spécialisés pour le Share-Ride (Partage)
        $shareRideTypes = [
            [
                'name' => 'Woro-Woro',
                'type' => 'standard',
                'provider_name' => 'Chauffeur Woro-Woro',
                'image' => 'service/woro_woro.png',
                'capacity' => 4,
                'allowed_variants' => ['arret_pdp', 'arret_hybride'],
                'sharing_type' => 'PDP',
                'arret_discount_percent' => 20.00,
                'price_per_segment' => 150,
                'km_per_segment' => 1.5,
                'fixed' => 150,
                'price' => 100, // Tarif km de repli
                'price_per_km' => 100.00,
                'calculator' => 'SHARED',
                'commission_percentage' => 10,
                'is_intercommunal' => 0,
                'is_communal' => 1,
                'max_distance' => 30,
                'max_detour_communal' => 2,
                'max_detour_intercommunal' => 0,
                'description' => 'Taxi partagé communal (Woro-Woro) pour déplacements rapides de gare à gare.',
                'status' => 1,
            ],
            [
                'name' => 'Bus Navette',
                'type' => 'standard',
                'provider_name' => 'Conducteur de Bus',
                'image' => 'service/bus.png',
                'capacity' => 20,
                'allowed_variants' => ['arret_pdp'], // Pas d'hybride detour pour le bus !
                'sharing_type' => 'PDP',
                'arret_discount_percent' => 0.00,
                'price_per_segment' => 100,
                'km_per_segment' => 3.0,
                'fixed' => 100,
                'price' => 50,
                'price_per_km' => 50.00,
                'calculator' => 'SHARED',
                'commission_percentage' => 5,
                'is_intercommunal' => 1,
                'is_communal' => 0,
                'max_distance' => 100,
                'max_detour_communal' => 0,
                'max_detour_intercommunal' => 0,
                'description' => 'Navette bus à grande capacité de gare à gare, sans détour hors-ligne.',
                'status' => 1,
            ],
            [
                'name' => 'PicME 7 Places',
                'type' => 'standard',
                'provider_name' => 'Chauffeur PicME',
                'image' => 'service/van_7places.png',
                'capacity' => 7,
                'allowed_variants' => ['arret_pdp', 'arret_hybride'],
                'sharing_type' => 'PDP',
                'arret_discount_percent' => 15.00,
                'price_per_segment' => 250,
                'km_per_segment' => 2.5,
                'fixed' => 250,
                'price' => 150,
                'price_per_km' => 150.00,
                'calculator' => 'SHARED',
                'commission_percentage' => 12,
                'is_intercommunal' => 1,
                'is_communal' => 0,
                'max_distance' => 150,
                'max_detour_communal' => 3,
                'max_detour_intercommunal' => 6,
                'description' => 'Minibus confortable 7 places pour liaisons intercommunales directes et hybrides.',
                'status' => 1,
            ]
        ];

        foreach ($shareRideTypes as $data) {
            // Supprimer le type existant de même nom sous Partage pour éviter les doublons
            $existing = ServiceType::where('name', $data['name'])->first();
            if ($existing) {
                DB::table('service_service_type')->where('service_type_id', $existing->id)->delete();
                $existing->delete();
            }

            // Créer le nouveau type de service
            $st = ServiceType::create([
                'name' => $data['name'],
                'type' => $data['type'],
                'provider_name' => $data['provider_name'],
                'image' => $data['image'],
                'capacity' => $data['capacity'],
                'allowed_variants' => $data['allowed_variants'],
                'sharing_type' => $data['sharing_type'],
                'arret_discount_percent' => $data['arret_discount_percent'],
                'price_per_segment' => $data['price_per_segment'],
                'km_per_segment' => $data['km_per_segment'],
                'fixed' => $data['fixed'],
                'price' => $data['price'],
                'price_per_km' => $data['price_per_km'],
                'calculator' => $data['calculator'],
                'commission_percentage' => $data['commission_percentage'],
                'is_intercommunal' => $data['is_intercommunal'],
                'is_communal' => $data['is_communal'],
                'max_distance' => $data['max_distance'],
                'max_detour_communal' => $data['max_detour_communal'],
                'max_detour_intercommunal' => $data['max_detour_intercommunal'],
                'description' => $data['description'],
                'status' => $data['status'],
                
                // Champs obligatoires sans valeurs par défaut
                'distance' => 1,
                'minute' => 0,
                'hour' => 0,
                'day' => 0,
                'requires_pro_subscription' => 0,
                'requires_feeder_ride' => 0,
                'can_act_as_feeder' => 0,
                'feeder_trigger_radius' => 0,
                'eco_discount_percent' => 0,
                'ambulance' => 0,
                'rental_amount' => 0,
                'outstation_price' => 0.00
            ]);

            DB::table('service_service_type')->insert([
                'service_id' => $partageService->id,
                'service_type_id' => $st->id,
                'name' => $data['name'],
                'provider_name' => $data['provider_name'],
                'image' => $data['image'],
                'capacity' => $data['capacity'],
                'fixed' => $data['fixed'],
                'price' => $data['price'],
                'minute' => 0,
                'hour' => 0,
                'distance' => 1,
                'calculator' => 'DISTANCEMIN', // Compatible avec l'enum restreint du pivot
                'description' => $data['description'],
                'status' => $data['status'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        if (\DB::getDriverName() === 'pgsql') { \DB::statement("SET session_replication_role = 'origin';"); } else { \DB::statement('SET FOREIGN_KEY_CHECKS=1;'); }
    }
}
