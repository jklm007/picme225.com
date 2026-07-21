<?php

namespace Database\Seeders;

use App\Models\ServiceType;
use Illuminate\Database\Seeder;

class PartageServiceTypesSeeder extends Seeder
{
    /**
     * Execute the seeding.
     */
    public function run(): void
    {
        $services = [
            [
                'name' => 'Woroworo',
                'provider_name' => 'Woroworo',
                'image' => 'service/woroworo.png',
                'calculator' => 'DISTANCE',
                'fixed' => 100,
                'price' => 50,
                'minute' => 10,
                'distance' => 10,
                'capacity' => 15,
                'description' => 'Trajets partagés en minibus.',
                'sharing_type' => 'PDP',
                'status' => 1,
                'free_km_per_passenger' => 2,
                'price_per_segment' => 150,
                'max_detour_communal' => 5,
                'max_detour_intercommunal' => 10,
            ],
            [
                'name' => 'Bus',
                'provider_name' => 'Bus',
                'image' => 'service/bus.png',
                'calculator' => 'DISTANCE',
                'fixed' => 150,
                'price' => 40,
                'minute' => 15,
                'distance' => 15,
                'capacity' => 30,
                'description' => 'Lignes partagées en bus.',
                'sharing_type' => 'DYNAMIC_POOL',
                'status' => 1,
                'free_km_per_passenger' => 3,
                'price_per_segment' => 120,
                'max_detour_communal' => 8,
                'max_detour_intercommunal' => 12,
            ],
            [
                'name' => 'Taxi Communal',
                'provider_name' => 'Taxi Communal',
                'image' => 'service/taxi_communal.png',
                'calculator' => 'DISTANCEMIN',
                'fixed' => 200,
                'price' => 60,
                'minute' => 20,
                'distance' => 20,
                'capacity' => 4,
                'description' => 'Taxi partagé intra-urbain.',
                'sharing_type' => 'PDP',
                'status' => 1,
                'free_km_per_passenger' => 1,
                'price_per_segment' => 200,
                'max_detour_communal' => 4,
                'max_detour_intercommunal' => 8,
            ],
            [
                'name' => 'Intercommunal',
                'provider_name' => 'Intercommunal',
                'image' => 'service/intercommunal.png',
                'calculator' => 'DISTANCE',
                'fixed' => 250,
                'price' => 30,
                'minute' => 25,
                'distance' => 25,
                'capacity' => 50,
                'description' => 'Lignes interurbaines partagées.',
                'sharing_type' => 'DYNAMIC_POOL',
                'status' => 1,
                'free_km_per_passenger' => 5,
                'price_per_segment' => 180,
                'max_detour_communal' => 6,
                'max_detour_intercommunal' => 12,
            ],
            [
                'name' => 'Livraison Partagée',
                'provider_name' => 'Livraison Partagée',
                'image' => 'service/livraison_partagee.png',
                'calculator' => 'DISTANCE',
                'fixed' => 120,
                'price' => 70,
                'minute' => 12,
                'distance' => 12,
                'capacity' => 1,
                'description' => 'Livraison multi-colis.',
                'sharing_type' => 'PDP',
                'status' => 1,
                'free_km_per_passenger' => 0,
                'price_per_segment' => 250,
                'max_detour_communal' => 5,
                'max_detour_intercommunal' => 10,
            ],
        ];

        foreach ($services as $service) {
            ServiceType::updateOrCreate(
                ['name' => $service['name']],
                $service
            );
        }
    }
}

