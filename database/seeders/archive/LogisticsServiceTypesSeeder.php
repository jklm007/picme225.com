<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LogisticsServiceTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // 1. Ensure the "Livraison" main category exists in services table
        $livraisonService = Service::updateOrCreate(
            ['name' => 'Livraison'],
            ['image' => 'service/delivery_main.png']
        );

        $subServices = [
            [
                'name' => 'Livraison Moto',
                'provider_name' => 'Livreur Moto',
                'image' => asset('asset/img/cars/moto_delivery.png'),
                'fixed' => 500,
                'price' => 150,
                'minute' => 0,
                'distance' => 1,
                'calculator' => 'DISTANCE',
                'capacity' => 1,
                'description' => 'Livraison directe et rapide par moto.',
                'status' => 1,
            ],
            [
                'name' => 'Livraison Van',
                'provider_name' => 'Livreur Van',
                'image' => asset('asset/img/cars/van_delivery.png'),
                'fixed' => 1000,
                'price' => 250,
                'minute' => 0,
                'distance' => 1,
                'calculator' => 'DISTANCE',
                'capacity' => 1,
                'description' => 'Idéal pour les colis volumineux ou fragiles.',
                'status' => 1,
            ],
            [
                'name' => 'Expédition Gare',
                'provider_name' => 'Compagnie de Car',
                'image' => asset('asset/img/cars/bus_freight.png'),
                'fixed' => 500,
                'price' => 50,
                'minute' => 0,
                'distance' => 1,
                'calculator' => 'DISTANCE',
                'capacity' => 100,
                'description' => 'Envoyez votre colis de gare à gare via nos partenaires.',
                'status' => 1,
            ],
            [
                'name' => 'Collecte + Expédition',
                'provider_name' => 'Super Logistique',
                'image' => asset('asset/img/cars/combined_logistics.png'),
                'fixed' => 1300,
                'price' => 60,
                'minute' => 0,
                'distance' => 1,
                'calculator' => 'DISTANCE',
                'capacity' => 1,
                'description' => 'Un motard récupère votre colis et le dépose en gare pour vous.',
                'status' => 1,
            ]
        ];

        foreach ($subServices as $data) {
            $serviceType = ServiceType::updateOrCreate(
                ['name' => $data['name']],
                $data
            );

            // 2. Link to the "Livraison" main category with full pivot data
            DB::table('service_service_type')->updateOrInsert(
                [
                    'service_id' => $livraisonService->id,
                    'service_type_id' => $serviceType->id
                ],
                [
                    'name' => $data['name'],
                    'provider_name' => $data['provider_name'],
                    'image' => $data['image'],
                    'capacity' => $data['capacity'],
                    'fixed' => $data['fixed'],
                    'price' => $data['price'],
                    'minute' => $data['minute'],
                    'distance' => $data['distance'],
                    'calculator' => $data['calculator'],
                    'description' => $data['description'],
                    'status' => 1,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
