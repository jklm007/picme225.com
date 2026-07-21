<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceType;
use App\Models\KmHour;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CustomServiceTypesSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Nettoyage
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("TRUNCATE TABLE service_service_type, service_type_rentals, km_hour_service_type_prices, service_types, services CASCADE;");
        } else {
            DB::statement("TRUNCATE TABLE service_service_type;");
            DB::statement("TRUNCATE TABLE service_type_rentals;");
            DB::statement("TRUNCATE TABLE km_hour_service_type_prices;");
            DB::statement("TRUNCATE TABLE service_types;");
            DB::statement("TRUNCATE TABLE services;");
        }

        // 2. Création des 6 catégories demandées (alignées sur PicmeServiceTypesSeeder)
        $categories = [
            'Taxi'      => 'service/taxi.png',
            'Livraison' => 'service/livraison.png',
            'Location'  => 'service/location.png',
            'Voyage'    => 'service/voyage.png',
            'Urgence'   => 'service/urgence.png',
            'Partage'   => 'service/eco_partage.png',
        ];

        $serviceMap = [];
        foreach ($categories as $catName => $catImage) {
            $svc = Service::create(['name' => $catName, 'image' => $catImage]);
            $serviceMap[$catName] = $svc;
        }

        // 3. TAXI - Types de véhicules
        $standardTypes = [
            [
                'name' => 'Taxi Vtc',
                'type' => 'standard',
                'provider_name' => 'Chauffeur',
                'image' => 'service/taxi_vtc.webp',
                'capacity' => 1,
                'allowed_variants' => ['prive', 'partage', 'arret_pdp'],
                'sharing_type' => 'PDP',
                'arret_discount_percent' => 15.00,
                'price_per_segment' => 500,
                'km_per_segment' => 4,
                'fixed' => 200,
                'price' => 200,
                'minute' => 5,
                'distance' => 1,
                'calculator' => 'DISTANCE',
                'commission_percentage' => 15,
                'is_intercommunal' => 1,
                'is_communal' => 0,
                'max_distance' => 100,
                'max_detour_communal' => 3,
                'max_detour_intercommunal' => 5,
                'description' => 'Berline confortable climatisée.',
                'status' => 1,
            ],
            [
                'name' => 'Taxi Compteur',
                'type' => 'standard',
                'provider_name' => 'Chauffeur',
                'image' => 'service/taxi_orange.webp',
                'capacity' => 4,
                'allowed_variants' => ['prive', 'partage', 'arret_pdp'],
                'sharing_type' => 'PDP',
                'arret_discount_percent' => 20.00,
                'price_per_segment' => 400,
                'km_per_segment' => 4,
                'fixed' => 500,
                'price' => 350,
                'minute' => 10,
                'distance' => 1,
                'calculator' => 'DISTANCE',
                'commission_percentage' => 15,
                'is_intercommunal' => 1,
                'is_communal' => 0,
                'max_distance' => 100,
                'max_detour_communal' => 3,
                'max_detour_intercommunal' => 5,
                'description' => 'Taxi rouge confortable sans clim.',
                'status' => 1,
            ],
            [
                'name' => 'Inter-communal',
                'type' => 'standard',
                'provider_name' => 'Chauffeur',
                'image' => 'service/inter-communal.webp',
                'capacity' => 7,
                'allowed_variants' => ['partage', 'arret_pdp'],
                'sharing_type' => 'PDP',
                'arret_discount_percent' => 15.00,
                'price_per_segment' => 300,
                'km_per_segment' => 2.5,
                'fixed' => 700,
                'price' => 500,
                'minute' => 15,
                'distance' => 1,
                'calculator' => 'DISTANCEMIN',
                'commission_percentage' => 15,
                'is_intercommunal' => 1,
                'is_communal' => 0,
                'max_distance' => 500,
                'max_detour_communal' => 5,
                'max_detour_intercommunal' => 10,
                'description' => 'van spacieux pour 7 passagers.',
                'status' => 1,
            ],
            [
                'name' => 'SUV',
                'type' => 'standard',
                'provider_name' => 'Chauffeur',
                'image' => 'service/suv.webp',
                'capacity' => 7,
                'allowed_variants' => ['prive', 'partage', 'arret_pdp'],
                'sharing_type' => 'PDP',
                'arret_discount_percent' => 15.00,
                'price_per_segment' => 600,
                'km_per_segment' => 4,
                'fixed' => 700,
                'price' => 500,
                'minute' => 15,
                'distance' => 1,
                'calculator' => 'DISTANCEMIN',
                'commission_percentage' => 15,
                'is_intercommunal' => 1,
                'is_communal' => 0,
                'max_distance' => 200,
                'max_detour_communal' => 5,
                'max_detour_intercommunal' => 10,
                'description' => 'nonospace spacieux pour 7 passagers.',
                'status' => 1,
            ],
            [
                'name' => 'Woro-Woro',
                'type' => 'standard',
                'provider_name' => 'Chauffeur',
                'image' => 'service/woro-woro.webp',
                'capacity' => 4,
                'allowed_variants' => ['prive', 'partage', 'arret_pdp'],
                'sharing_type' => 'PDP',
                'arret_discount_percent' => 30.00,
                'price_per_segment' => 200,
                'km_per_segment' => 2.5,
                'fixed' => 300,
                'price' => 200,
                'minute' => 5,
                'distance' => 1,
                'calculator' => 'DISTANCE',
                'commission_percentage' => 10,
                'is_intercommunal' => 0,
                'is_communal' => 1,
                'max_distance' => 50,
                'max_detour_communal' => 5,
                'max_detour_intercommunal' => 0,
                'description' => 'Transport communal partagé traditionnel.',
                'status' => 1,
            ],
        ];

        foreach ($standardTypes as $data) {
            $st = ServiceType::create($data);
            DB::table('service_service_type')->insert(
                array_merge($this->pivotBase($data), [
                    'service_id' => $serviceMap['Taxi']->id,
                    'service_type_id' => $st->id,
                    'updated_at' => now(),
                    'created_at' => now()
                ])
            );
        }

        // 4. LIVRAISON - Types de véhicules
        $livraisonTypes = [
            [
                'name' => 'Moto',
                'type' => 'livraison',
                'provider_name' => 'Livreur Moto',
                'image' => 'service/moto.webp',
                'capacity' => 1,
                'allowed_variants' => ['prive'],
                'fixed' => 500,
                'price' => 150,
                'distance' => 1,
                'calculator' => 'DISTANCE',
                'commission_percentage' => 15,
                'is_intercommunal' => 1,
                'is_communal' => 0,
                'max_distance' => 50,
                'description' => 'Livraison rapide de petits colis.',
                'status' => 1,
            ],
            [
                'name' => 'Cargo',
                'type' => 'livraison',
                'provider_name' => 'Livreur Van',
                'image' => 'service/cargo.webp',
                'capacity' => 1,
                'allowed_variants' => ['prive'],
                'fixed' => 2000,
                'price' => 400,
                'distance' => 1,
                'calculator' => 'DISTANCE',
                'commission_percentage' => 15,
                'is_intercommunal' => 1,
                'is_communal' => 0,
                'max_distance' => 200,
                'description' => 'Livraison de marchandises volumineuses.',
                'status' => 1,
            ],
        ];

        foreach ($livraisonTypes as $data) {
            $st = ServiceType::create($data);
            DB::table('service_service_type')->insert(
                array_merge($this->pivotBase($data), [
                    'service_id' => $serviceMap['Livraison']->id,
                    'service_type_id' => $st->id,
                    'updated_at' => now(),
                    'created_at' => now()
                ])
            );
        }

        // 5. LOCATION - Types de véhicules
        $rentalTypes = [
            [
                'name' => 'Berline',
                'type' => 'rental',
                'provider_name' => 'Chauffeur Location',
                'image' => 'service/taxi_vtc.webp',
                'capacity' => 4,
                'allowed_variants' => ['prive'],
                'fixed' => 0,
                'price' => 0,
                'hour' => 5000,
                'distance' => 1,
                'calculator' => 'HOUR',
                'rental_amount' => 5000,
                'commission_percentage' => 15,
                'is_intercommunal' => 1,
                'is_communal' => 0,
                'max_distance' => 0, // Illimité
                'description' => 'Location berline à l\'heure. Chauffeur inclus.',
                'status' => 1,
            ],
            [
                'name' => 'SUV',
                'type' => 'rental',
                'provider_name' => 'Chauffeur Location',
                'image' => 'service/van.webp',
                'capacity' => 7,
                'allowed_variants' => ['prive'],
                'fixed' => 0,
                'price' => 0,
                'hour' => 8000,
                'distance' => 1,
                'calculator' => 'HOUR',
                'rental_amount' => 8000,
                'commission_percentage' => 15,
                'is_intercommunal' => 1,
                'is_communal' => 0,
                'max_distance' => 0,
                'description' => 'Location SUV à l\'heure.',
                'status' => 1,
            ],
        ];

        foreach ($rentalTypes as $data) {
            $st = ServiceType::create($data);
            DB::table('service_service_type')->insert(
                array_merge($this->pivotBase($data), [
                    'service_id' => $serviceMap['Location']->id,
                    'service_type_id' => $st->id,
                    'rental_amount' => $data['rental_amount'] ?? 0,
                    'updated_at' => now(),
                    'created_at' => now()
                ])
            );

            // Ajout des forfaits (km_hour_service_type_prices)
            $packages = KmHour::all();
            foreach ($packages as $pkg) {
                // Calcul du prix du forfait basé sur le prix horaire du véhicule
                // On applique une petite dégressivité pour les longs forfaits
                $baseHourPrice = $data['rental_amount'];
                $totalPrice = $baseHourPrice * $pkg->hour;

                if ($pkg->hour >= 4) $totalPrice *= 0.9;  // -10%
                if ($pkg->hour >= 12) $totalPrice *= 0.8; // -20%
                if ($pkg->hour >= 24) $totalPrice *= 0.7; // -30%

                DB::table('km_hour_service_type_prices')->insert([
                    'km_hour_id' => $pkg->id,
                    'service_type_id' => $st->id,
                    'price' => round($totalPrice),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 6. URGENCE - Types de véhicules
        $urgenceTypes = [
            [
                'name' => 'Ambulance',
                'type' => 'standard',
                'provider_name' => 'Ambulancier',
                'image' => 'service/ambulance.webp',
                'capacity' => 1,
                'allowed_variants' => ['prive'],
                'fixed' => 5000,
                'price' => 500,
                'distance' => 1,
                'calculator' => 'DISTANCE',
                'commission_percentage' => 0,
                'is_intercommunal' => 1,
                'is_communal' => 0,
                'ambulance' => 1,
                'max_distance' => 200,
                'description' => 'Service d\'ambulance d\'urgence.',
                'status' => 1,
            ],
        ];

        foreach ($urgenceTypes as $data) {
            $st = ServiceType::create($data);
            DB::table('service_service_type')->insert(
                array_merge($this->pivotBase($data), [
                    'service_id' => $serviceMap['Urgence']->id,
                    'service_type_id' => $st->id,
                    'updated_at' => now(),
                    'created_at' => now()
                ])
            );
        }


        // 7. VOYAGE - Types de véhicules (longue distance / interurbain)
        $voyageTypes = [
            [
                'name'                 => 'Berline Voyage',
                'type'                 => 'standard',
                'provider_name'        => 'Chauffeur',
                'image'                => 'service/berline.webp',
                'capacity'             => 4,
                'allowed_variants'     => ['prive'],
                'fixed'                => 3000,
                'price'                => 300,
                'minute'               => 0,
                'distance'             => 1,
                'calculator'           => 'DISTANCE',
                'commission_percentage'=> 15,
                'is_intercommunal'     => 1,
                'is_communal'          => 0,
                'is_intercity'         => 1,
                'max_distance'         => 500,
                'description'          => 'Berline confortable pour voyages longue distance.',
                'status'               => 1,
            ],
            [
                'name'                 => 'SUV Voyage',
                'type'                 => 'standard',
                'provider_name'        => 'Chauffeur',
                'image'                => 'service/van.webp',
                'capacity'             => 7,
                'allowed_variants'     => ['prive'],
                'fixed'                => 5000,
                'price'                => 450,
                'minute'               => 0,
                'distance'             => 1,
                'calculator'           => 'DISTANCE',
                'commission_percentage'=> 15,
                'is_intercommunal'     => 1,
                'is_communal'          => 0,
                'is_intercity'         => 1,
                'max_distance'         => 500,
                'description'          => 'SUV spacieux pour voyages en famille.',
                'status'               => 1,
            ],
        ];

        foreach ($voyageTypes as $data) {
            $st = ServiceType::create($data);
            DB::table('service_service_type')->insert(
                array_merge($this->pivotBase($data), [
                    'service_id'      => $serviceMap['Voyage']->id,
                    'service_type_id' => $st->id,
                    'updated_at'      => now(),
                    'created_at'      => now()
                ])
            );
        }


    }

    private function pivotBase(array $data): array
    {
        return [
            'name' => $data['name'] ?? '',
            'provider_name' => $data['provider_name'] ?? null,
            'image' => $data['image'] ?? null,
            'capacity' => $data['capacity'] ?? 0,
            'fixed' => $data['fixed'] ?? 0,
            'price' => $data['price'] ?? 0,
            'minute' => $data['minute'] ?? 0,
            'hour' => $data['hour'] ?? null,
            'distance' => $data['distance'] ?? 1,
            'calculator' => $data['calculator'] ?? 'DISTANCE',
            'description' => $data['description'] ?? '',
            'status' => $data['status'] ?? 1,
        ];
    }
}
