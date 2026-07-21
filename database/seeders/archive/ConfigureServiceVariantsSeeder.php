<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConfigureServiceVariantsSeeder extends Seeder
{
    public function run()
    {
        // Configuration des variantes autorisées par service selon les nouvelles spécifications
        $configurations = [
            'Standard' => [
                'allowed_variants' => ['prive', 'partage'],
                'arret_discount_percent' => null
            ],
            'Taxi' => [
                'allowed_variants' => ['prive', 'partage'],
                'arret_discount_percent' => null
            ],
            'Share-Ride' => [
                'allowed_variants' => ['arret', 'partage'], // Segments only (arret) or Segments + Detour (partage)
                'arret_discount_percent' => null
            ],
            'Voyage' => [
                'allowed_variants' => ['prive', 'arret'],
                'arret_discount_percent' => null // Utilise prix segmenté pour Arrêt
            ],
            'Delivery' => [
                'allowed_variants' => ['prive'],
                'arret_discount_percent' => null
            ]
        ];

        foreach ($configurations as $serviceName => $config) {
            DB::table('service_types')
                ->where('name', 'LIKE', "%{$serviceName}%")
                ->update([
                    'allowed_variants' => json_encode($config['allowed_variants']),
                    'arret_discount_percent' => $config['arret_discount_percent']
                ]);
        }

        $this->command->info('Configuration des variantes par service terminée !');
    }
}
