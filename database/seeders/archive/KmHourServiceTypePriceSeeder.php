<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\KmHourServiceTypePrice; // <<< AJOUTER CETTE LIGNE

class KmHourServiceTypePriceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Forfait 1: 2h/100km pour le ServiceType SUV (ID 16)
        KmHourServiceTypePrice::updateOrCreate(
            [
                'km_hour_id' => 2,
                'service_type_id' => 16
            ],
            [
                'price' => 20000.00
            ]
        );

        // Forfait 2: 12h/200km pour le ServiceType SUV (ID 16)
        KmHourServiceTypePrice::updateOrCreate(
            [
                'km_hour_id' => 4,
                'service_type_id' => 16
            ],
            [
                'price' => 75000.00
            ]
        );

        // Forfait 3: 12h/500km pour le ServiceType SUV (ID 16)
        KmHourServiceTypePrice::updateOrCreate(
            [
                'km_hour_id' => 5,
                'service_type_id' => 16
            ],
            [
                'price' => 150000.00 // Prix exemple
            ]
        );

         // Forfait 4: 48h/1000km pour le ServiceType SUV (ID 16)
         KmHourServiceTypePrice::updateOrCreate(
            [
                'km_hour_id' => 6,
                'service_type_id' => 16
            ],
            [
                'price' => 250000.00 // Prix exemple
            ]
        );


        // OPTIONNEL: Ajoutez des prix pour un AUTRE type de véhicule, par exemple une "Berline" (supposons ID 17)
        /*
        KmHourServiceTypePrice::updateOrCreate(
            [
                'km_hour_id' => 2, // Le même forfait 2h/100km
                'service_type_id' => 17 // Mais pour la Berline (ID 17)
            ],
            [
                'price' => 15000.00 // Un prix différent
            ]
        );
        */

        // Afficher un message dans la console pour confirmer que le seeder a été exécuté
        $this->command->info('KmHour service type prices seeded successfully!');
    }
}
