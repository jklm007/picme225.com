<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Service;
use App\Models\ServiceType;
use Carbon\Carbon;

class PickmeTransportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Cette classe configure exactement les catégories (Services) et véhicules (ServiceTypes)
     * demandés pour PickMe.
     *
     * @return void
     */
    public function run()
    {
        // 1. Désactiver les contraintes de clés étrangères
        if (\DB::getDriverName() === 'mysql') { \if (\DB::getDriverName() === 'mysql') { \DB::statement('SET FOREIGN_KEY_CHECKS=0;'); } elseif (\DB::getDriverName() === 'pgsql') { \DB::statement("SET session_replication_role = 'replica';"); } } elseif (\DB::getDriverName() === 'pgsql') { \DB::statement("SET session_replication_role = 'replica';"); }

        // 2. Vider les anciennes tables
        Service::truncate();
        DB::table('service_types')->truncate();
        DB::table('service_service_type')->truncate();

        if (\DB::getDriverName() === 'mysql') { \if (\DB::getDriverName() === 'mysql') { \DB::statement('SET FOREIGN_KEY_CHECKS=1;'); } elseif (\DB::getDriverName() === 'pgsql') { \DB::statement("SET session_replication_role = 'origin';"); } } elseif (\DB::getDriverName() === 'pgsql') { \DB::statement("SET session_replication_role = 'origin';"); }

        // ---------------------------------------------------------
        // 3. CREATION DES CATEGORIES PRINCIPALES (SERVICES)
        // ---------------------------------------------------------
        $mainCategories = [
            ['name' => 'Transport', 'image' => 'standard.jpg'], // Courses immédiates
            ['name' => 'Location', 'image' => 'rental.jpg'],    // Réservation à l'heure/jour
            ['name' => 'Hors-Ville', 'image' => 'outstation.jpg'] // Trajets lointains (optionnel)
        ];

        foreach ($mainCategories as $cat) {
            Service::create($cat);
        }

        // Récupérer les catégories pour les attacher plus tard
        $transportService = Service::where('name', 'Transport')->first();
        $locationService = Service::where('name', 'Location')->first();


        // ---------------------------------------------------------
        // 4. CREATION DES TYPES DE VEHICULES (SERVICE TYPES)
        // ---------------------------------------------------------
        $now = Carbon::now();

        // -- VEHICULES POUR LE TRANSPORT STRICT --
        $transportVehicles = [
            [
                'name' => 'Woro-woro',
                'provider_name' => 'Chauffeur',
                'fixed' => 500, // Prix de base
                'price' => 100, // Prix par Km ou minute
                'status' => 1,
                'minute' => 0, 'distance' => '1',
                'calculator' => 'DISTANCE',
                'image' => 'asset/img/cars/woro.png',
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'name' => 'Taxi VTC',
                'provider_name' => 'Chauffeur',
                'fixed' => 1000,
                'price' => 250,
                'status' => 1,
                'minute' => 0, 'distance' => '1',
                'calculator' => 'DISTANCE',
                'image' => 'asset/img/cars/vtc.png',
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'name' => 'Taxi compteur',
                'provider_name' => 'Chauffeur',
                'fixed' => 800,
                'price' => 200,
                'status' => 1,
                'minute' => 0, 'distance' => '1',
                'calculator' => 'MIN',
                'image' => 'asset/img/cars/taxicompteur.png',
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'name' => 'SUV',
                'provider_name' => 'Chauffeur VIP',
                'fixed' => 2500,
                'price' => 500,
                'status' => 1,
                'minute' => 0, 'distance' => '1',
                'calculator' => 'DISTANCE',
                'image' => 'asset/img/cars/suv.png',
                'created_at' => $now, 'updated_at' => $now,
            ],
        ];

        // -- VEHICULES POUR LA LOCATION --
        $locationVehicles = [
            [
                'name' => 'Van',
                'provider_name' => 'Chauffeur',
                'fixed' => 0, 'price' => 0, 'status' => 1, 'rental_amount' => 15000, 'calculator' => 'HOUR',
                'minute' => 0, 'distance' => '1',
                'image' => 'asset/img/cars/van.png',
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'name' => 'Pickup',
                'provider_name' => 'Chauffeur',
                'fixed' => 0, 'price' => 0, 'status' => 1, 'rental_amount' => 20000, 'calculator' => 'HOUR',
                'minute' => 0, 'distance' => '1',
                'image' => 'asset/img/cars/pickup.png',
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'name' => 'Moto',
                'provider_name' => 'Livreur/Pilote',
                'fixed' => 0, 'price' => 0, 'status' => 1, 'rental_amount' => 5000, 'calculator' => 'HOUR',
                'minute' => 0, 'distance' => '1',
                'image' => 'asset/img/cars/moto.png',
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'name' => 'Scooter',
                'provider_name' => 'Livreur/Pilote',
                'fixed' => 0, 'price' => 0, 'status' => 1, 'rental_amount' => 4500, 'calculator' => 'HOUR',
                'minute' => 0, 'distance' => '1',
                'image' => 'asset/img/cars/scooter.png',
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'name' => 'Mini-bus',
                'provider_name' => 'Chauffeur',
                'fixed' => 0, 'price' => 0, 'status' => 1, 'rental_amount' => 30000, 'calculator' => 'DAY', // Location par jour par ex.
                'minute' => 0, 'distance' => '1',
                'image' => 'asset/img/cars/minibus.png',
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'name' => 'Bus (Car de voyage)',
                'provider_name' => 'Transporteur',
                'fixed' => 0, 'price' => 0, 'status' => 1, 'rental_amount' => 80000, 'calculator' => 'DAY',
                'minute' => 0, 'distance' => '1',
                'image' => 'asset/img/cars/bus.png',
                'created_at' => $now, 'updated_at' => $now,
            ],
        ];

        // ---------------------------------------------------------
        // 5. INSERTION ET ASSOCIATION PILE AU BON ENDROIT
        // ---------------------------------------------------------

        // 5.A. Attacher les véhicules de TRANSPORT
        foreach ($transportVehicles as $vData) {
            $typeId = DB::table('service_types')->insertGetId($vData);

            $transportService->serviceTypes()->attach($typeId, [
                'fixed' => $vData['fixed'],
                'price' => $vData['price'],
                'calculator' => $vData['calculator'],
                'minute' => 0,
                'distance' => '1',
                'description' => "Course en {$vData['name']}",
                'status' => 1,
                'name' => "Transport - {$vData['name']}"
            ]);
        }

        // 5.B. Attacher les véhicules de LOCATION
        foreach ($locationVehicles as $vData) {
            $typeId = DB::table('service_types')->insertGetId($vData);

            $locationService->serviceTypes()->attach($typeId, [
                'fixed' => $vData['fixed'],
                'price' => $vData['price'],
                'rental_amount' => $vData['rental_amount'],
                'minute' => 0,
                'distance' => '1',
                'calculator' => in_array($vData['calculator'], ['MIN','HOUR','DISTANCE','DISTANCEMIN','DISTANCEDAY'])
                    ? $vData['calculator'] : 'DISTANCEDAY',
                'description' => "Location de {$vData['name']}",
                'status' => 1,
                'name' => "Location - {$vData['name']}"
            ]);
        }

        // Optionnel: Si un SUV peut faire à la fois Transport ET Location,
        // On récupère son ID et on l'attache aussi à Location avec un prix de Rental
        $suvType = ServiceType::where('name', 'SUV')->first();
        if ($suvType) {
            $locationService->serviceTypes()->attach($suvType->id, [
                'fixed' => 0,
                'price' => 0,
                'rental_amount' => 40000,
                'minute' => 0,
                'distance' => '1',
                'calculator' => 'DISTANCEDAY',
                'description' => "Location de SUV",
                'status' => 1,
                'name' => "Location - SUV"
            ]);
        }

        $this->command->info('Les catégories Transport et Location, ainsi que les types de véhicules (Woro-woro, VTC, Van, etc.) ont été configurés avec succès !');
    }
}
