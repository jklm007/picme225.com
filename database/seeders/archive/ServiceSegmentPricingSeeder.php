<?php

namespace Database\Seeders;

use App\Models\ServiceType;
use Illuminate\Database\Seeder;

class ServiceSegmentPricingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Définition personnalisée par ServiceType
        // Vous pouvez ajuster free_km_per_passenger pour chaque type séparément
        $pricing = [
            'Moto Taxi'      => [
                'free_km_per_passenger' => 1, // 1 km gratuit pour Moto
                'price_per_segment'     => 100
            ],
            'Berline'        => [
                'free_km_per_passenger' => 2, // 2 km gratuits pour Berline
                'price_per_segment'     => 250
            ],
            'SUV / 4x4'      => [
                'free_km_per_passenger' => 0, // Pas de km gratuit pour SUV
                'price_per_segment'     => 400
            ],
            'Premium VIP'    => [
                'free_km_per_passenger' => 5, // 5 km gratuits pour VIP !
                'price_per_segment'     => 1000
            ],
            'Gbaka'          => [
                'free_km_per_passenger' => 0,
                'price_per_segment'     => 100
            ],
            'Woro-Woro'      => [
                'free_km_per_passenger' => 0,
                'price_per_segment'     => 200
            ],
        ];

        foreach ($pricing as $name => $data) {
            ServiceType::where('name', $name)->update($data);
            $this->command->info("Mis à jour : $name (Km gratuits: {$data['free_km_per_passenger']})");
        }
    }
}
