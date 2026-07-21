<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;
use App\Models\ServiceType;
use Illuminate\Support\Facades\DB;

class OutstationPartnerServicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Trouver le service Outstation
        $outstationService = Service::where('name', 'Outstation')->first();
        if (!$outstationService) {
            $outstationService = Service::create(['name' => 'Outstation', 'image' => 'outstation.jpg']);
        }

        // 2. Liste des compagnies partenaires à ajouter comme ServiceTypes
        $companies = [
            ['name' => 'UTB', 'image' => 'utb_logo.png', 'price' => 5000],
            ['name' => 'AVS', 'image' => 'avs_logo.png', 'price' => 6000],
            ['name' => 'CTE', 'image' => 'cte_logo.png', 'price' => 5500],
            ['name' => 'BKT', 'image' => 'bkt_logo.png', 'price' => 5000],
            ['name' => 'MT Transport', 'image' => 'mt_logo.png', 'price' => 7000],
            ['name' => 'AHT', 'image' => 'aht_logo.png', 'price' => 5000],
            ['name' => 'TDF', 'image' => 'tdf_logo.png', 'price' => 4500],
            ['name' => 'SBTA', 'image' => 'sbta_logo.png', 'price' => 5500],
            ['name' => 'G3S', 'image' => 'g3s_logo.png', 'price' => 6000],
            ['name' => 'STIF', 'image' => 'stif_logo.png', 'price' => 5000],
            ['name' => 'SITRA', 'image' => 'sitra_logo.png', 'price' => 5500],
            ['name' => 'Labelle', 'image' => 'labelle_logo.png', 'price' => 6500],
            ['name' => 'GK', 'image' => 'gk_logo.png', 'price' => 5000],
            ['name' => 'Massa', 'image' => 'massa_logo.png', 'price' => 4000],
            ['name' => 'Express', 'image' => 'express_logo.png', 'price' => 5500],
        ];

        foreach ($companies as $comp) {
            // Créer le ServiceType
            $serviceType = ServiceType::updateOrCreate(
                ['name' => $comp['name']],
                [
                    'fixed' => $comp['price'],
                    'price' => $comp['price'],
                    'image' => $comp['image'],
                    'capacity' => 50,
                    'status' => 1,
                    'minute' => 0,
                    'hour' => 0,
                    'distance' => 1,
                    'day' => 0,
                    'calculator' => 'DISTANCE',
                    'allowed_variants' => ['arret', 'prive']
                ]
            );

            // Lier au service Outstation si pas déjà fait
            $exists = DB::table('service_service_type')
                ->where('service_id', $outstationService->id)
                ->where('service_type_id', $serviceType->id)
                ->exists();

            if (!$exists) {
                $outstationService->serviceTypes()->attach($serviceType->id, [
                    'name' => $comp['name'],
                    'fixed' => $comp['price'],
                    'price' => $comp['price'],
                    'minute' => 0,
                    'distance' => 1,
                    'calculator' => 'DISTANCE',
                    'status' => 1,
                    'description' => "Service hors ville " . $comp['name']
                ]);
            }

            // Créer aussi l'entrée dans interurban_companies pour la partie StationAgent
            \App\Models\InterurbanCompany::updateOrCreate(
                ['name' => $comp['name']],
                ['logo' => $comp['image'], 'is_active' => true]
            );
        }
    }
}
