<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PdpStopsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Note: Uses DB::table() directly to avoid PdpStop model's forced pgsql connection.
     */
    public function run(): void
    {
        $stops = [
            [
                'name'      => 'Pharmacie St Jean de Cocody',
                'address'   => 'Cocody, Abidjan',
                'latitude'  => 5.3620,
                'longitude' => -3.9890,
                'commune'   => 'Cocody',
            ],
            [
                'name'      => 'Carrefour Vie',
                'address'   => 'Cocody, Abidjan',
                'latitude'  => 5.3640,
                'longitude' => -3.9870,
                'commune'   => 'Cocody',
            ],
            [
                'name'      => 'Carrefour MOBILE',
                'address'   => 'Cocody, Abidjan',
                'latitude'  => 5.3650,
                'longitude' => -3.9850,
                'commune'   => 'Cocody',
            ],
            [
                'name'      => 'Carrefour OPERA',
                'address'   => 'Cocody, Abidjan',
                'latitude'  => 5.3654,
                'longitude' => -3.9823,
                'commune'   => 'Cocody',
            ],
            [
                'name'      => 'Carrefour du 22e Arrondissement',
                'address'   => 'Cocody, Abidjan',
                'latitude'  => 5.3660,
                'longitude' => -3.9810,
                'commune'   => 'Cocody',
            ],
            [
                'name'      => 'Carrefour PETRO IVOIRE',
                'address'   => 'Angré, Abidjan',
                'latitude'  => 5.3670,
                'longitude' => -3.9790,
                'commune'   => 'Cocody',
            ],
            [
                'name'      => 'Carrefour CHATEAU',
                'address'   => 'Angré, Abidjan',
                'latitude'  => 5.3680,
                'longitude' => -3.9770,
                'commune'   => 'Cocody',
            ],
            [
                'name'      => 'Carrefour fin goudron',
                'address'   => 'Angré, Abidjan',
                'latitude'  => 5.3700,
                'longitude' => -3.9750,
                'commune'   => 'Cocody',
            ],
            [
                'name'      => 'Feu tricolore de la Riviera 2',
                'address'   => 'Riviera 2, Abidjan',
                'latitude'  => 5.3556,
                'longitude' => -3.9712,
                'commune'   => 'Cocody',
            ],
            [
                'name'      => 'Carrefour marché Anono',
                'address'   => 'Anono, Abidjan',
                'latitude'  => 5.3508,
                'longitude' => -3.9805,
                'commune'   => 'Cocody',
            ],
            [
                'name'      => 'Feu tricolore Paroisse Saint Jean',
                'address'   => 'Cocody, Abidjan',
                'latitude'  => 5.3465,
                'longitude' => -3.9960,
                'commune'   => 'Cocody',
            ],
            [
                'name'      => 'Feu du Zoo',
                'address'   => 'Cocody, Abidjan',
                'latitude'  => 5.3500,
                'longitude' => -4.0050,
                'commune'   => 'Cocody',
            ],
            [
                'name'      => 'Carrefour Ste Cécile',
                'address'   => 'Cocody, Abidjan',
                'latitude'  => 5.3520,
                'longitude' => -4.0020,
                'commune'   => 'Cocody',
            ],
            [
                'name'      => 'Feu du 30e Arrondissement',
                'address'   => 'Attoban, Abidjan',
                'latitude'  => 5.3540,
                'longitude' => -4.0000,
                'commune'   => 'Cocody',
            ],
            [
                'name'      => 'Pharmacie St Bernard',
                'address'   => 'Attoban, Abidjan',
                'latitude'  => 5.3560,
                'longitude' => -3.9980,
                'commune'   => 'Cocody',
            ],
            [
                'name'      => 'Carrefour 2 Plateaux Agban',
                'address'   => '2 Plateaux, Abidjan',
                'latitude'  => 5.3580,
                'longitude' => -3.9900,
                'commune'   => 'Cocody',
            ],
            [
                'name'      => 'Station OLA',
                'address'   => '2 Plateaux Vallon, Abidjan',
                'latitude'  => 5.3600,
                'longitude' => -3.9880,
                'commune'   => 'Cocody',
            ],
            [
                'name'      => 'Ancienne banque NSIA Rue des Jardins',
                'address'   => 'Vallon, Abidjan',
                'latitude'  => 5.3620,
                'longitude' => -3.9860,
                'commune'   => 'Cocody',
            ],
            [
                'name'      => 'Carrefour 9 Kilos',
                'address'   => 'Bingerville, Abidjan',
                'latitude'  => 5.3620,
                'longitude' => -3.9200,
                'commune'   => 'Bingerville',
            ],
            [
                'name'      => 'Carrefour de la Mosquée',
                'address'   => 'Bingerville, Abidjan',
                'latitude'  => 5.3630,
                'longitude' => -3.9180,
                'commune'   => 'Bingerville',
            ],
            [
                'name'      => 'Gare STL',
                'address'   => 'Riviera 3, Abidjan',
                'latitude'  => 5.3640,
                'longitude' => -3.9160,
                'commune'   => 'Cocody',
            ],
            [
                'name'      => 'Rond-Point SODECI',
                'address'   => 'Palmeraie, Abidjan',
                'latitude'  => 5.3580,
                'longitude' => -3.9700,
                'commune'   => 'Cocody',
            ],
            [
                'name'      => 'Carrefour St Viateur',
                'address'   => 'Cocody, Abidjan',
                'latitude'  => 5.3590,
                'longitude' => -3.9680,
                'commune'   => 'Cocody',
            ],
            [
                'name'      => 'Carrefour CHU Angré',
                'address'   => 'Angré, Abidjan',
                'latitude'  => 5.3600,
                'longitude' => -3.9660,
                'commune'   => 'Cocody',
            ],
            [
                'name'      => 'Fin goudron',
                'address'   => 'Angré, Abidjan',
                'latitude'  => 5.3610,
                'longitude' => -3.9640,
                'commune'   => 'Cocody',
            ],
            // Yopougon
            [
                'name'      => 'Yopougon Sable',
                'address'   => 'Yopougon, Abidjan',
                'latitude'  => 5.3700,
                'longitude' => -4.0600,
                'commune'   => 'Yopougon',
            ],
            [
                'name'      => 'Yopougon 1er Pont',
                'address'   => 'Yopougon, Abidjan',
                'latitude'  => 5.3680,
                'longitude' => -4.0550,
                'commune'   => 'Yopougon',
            ],
            [
                'name'      => 'Attécoubé',
                'address'   => 'Attécoubé, Abidjan',
                'latitude'  => 5.3660,
                'longitude' => -4.0500,
                'commune'   => 'Attécoubé',
            ],
            [
                'name'      => 'Adjamé Renault',
                'address'   => 'Adjamé, Abidjan',
                'latitude'  => 5.3600,
                'longitude' => -4.0200,
                'commune'   => 'Adjamé',
            ],
            // Abobo
            [
                'name'      => 'Abobo Ancienne Gendarmerie',
                'address'   => 'Abobo, Abidjan',
                'latitude'  => 5.4200,
                'longitude' => -4.0100,
                'commune'   => 'Abobo',
            ],
            [
                'name'      => 'Abobo St Joseph',
                'address'   => 'Abobo, Abidjan',
                'latitude'  => 5.4180,
                'longitude' => -4.0120,
                'commune'   => 'Abobo',
            ],
            [
                'name'      => 'Carrefour Agripac',
                'address'   => 'Abobo, Abidjan',
                'latitude'  => 5.4160,
                'longitude' => -4.0140,
                'commune'   => 'Abobo',
            ],
            [
                'name'      => 'Adjamé Liberté',
                'address'   => 'Adjamé, Abidjan',
                'latitude'  => 5.3620,
                'longitude' => -4.0180,
                'commune'   => 'Adjamé',
            ],
            // Koumassi
            [
                'name'      => 'Grand Carrefour Koumassi',
                'address'   => 'Koumassi, Abidjan',
                'latitude'  => 5.2980,
                'longitude' => -3.9690,
                'commune'   => 'Koumassi',
            ],
            [
                'name'      => 'Koumassi Remblais',
                'address'   => 'Koumassi, Abidjan',
                'latitude'  => 5.3010,
                'longitude' => -3.9720,
                'commune'   => 'Koumassi',
            ],
            [
                'name'      => 'Marcory Station',
                'address'   => 'Marcory, Abidjan',
                'latitude'  => 5.3130,
                'longitude' => -3.9820,
                'commune'   => 'Marcory',
            ],
            [
                'name'      => 'Plateau Centre',
                'address'   => 'Plateau, Abidjan',
                'latitude'  => 5.3200,
                'longitude' => -4.0100,
                'commune'   => 'Plateau',
            ],
            // Yopougon Maroc
            [
                'name'      => 'Carrefour Maroc',
                'address'   => 'Yopougon, Abidjan',
                'latitude'  => 5.3750,
                'longitude' => -4.0700,
                'commune'   => 'Yopougon',
            ],
            [
                'name'      => 'Carrefour Antenne',
                'address'   => 'Yopougon, Abidjan',
                'latitude'  => 5.3760,
                'longitude' => -4.0720,
                'commune'   => 'Yopougon',
            ],
            [
                'name'      => 'Mairie Sideci',
                'address'   => 'Yopougon, Abidjan',
                'latitude'  => 5.3770,
                'longitude' => -4.0740,
                'commune'   => 'Yopougon',
            ],
            // Abobo Gare
            [
                'name'      => 'Mairie d\'Abobo',
                'address'   => 'Abobo, Abidjan',
                'latitude'  => 5.4210,
                'longitude' => -4.0200,
                'commune'   => 'Abobo',
            ],
            [
                'name'      => 'Gare SOTRA Abobo',
                'address'   => 'Abobo, Abidjan',
                'latitude'  => 5.4220,
                'longitude' => -4.0210,
                'commune'   => 'Abobo',
            ],
            [
                'name'      => 'Carrefour PK 18',
                'address'   => 'Abobo, Abidjan',
                'latitude'  => 5.4500,
                'longitude' => -4.0400,
                'commune'   => 'Abobo',
            ],
            // Bingerville
            [
                'name'      => 'Gare Woro-Woro Bingerville',
                'address'   => 'Bingerville, Abidjan',
                'latitude'  => 5.3530,
                'longitude' => -3.8900,
                'commune'   => 'Bingerville',
            ],
            [
                'name'      => 'Hôpital Mère-Enfant',
                'address'   => 'Bingerville, Abidjan',
                'latitude'  => 5.3620,
                'longitude' => -3.8810,
                'commune'   => 'Bingerville',
            ],
            [
                'name'      => 'Carrefour FHB Bingerville',
                'address'   => 'Bingerville, Abidjan',
                'latitude'  => 5.3550,
                'longitude' => -3.8850,
                'commune'   => 'Bingerville',
            ],
            [
                'name'      => 'CHU de Cocody',
                'address'   => 'Cocody, Abidjan',
                'latitude'  => 5.3580,
                'longitude' => -3.9600,
                'commune'   => 'Cocody',
            ],
            // Port-Bouet
            [
                'name'      => 'Carrefour Gonzagueville',
                'address'   => 'Port-Bouët, Abidjan',
                'latitude'  => 5.2340,
                'longitude' => -3.9210,
                'commune'   => 'Port-Bouët',
            ],
            [
                'name'      => 'Phare de Port-Bouët',
                'address'   => 'Port-Bouët, Abidjan',
                'latitude'  => 5.2520,
                'longitude' => -3.9510,
                'commune'   => 'Port-Bouët',
            ],
            [
                'name'      => 'Aéroport FHB',
                'address'   => 'Port-Bouët, Abidjan',
                'latitude'  => 5.2610,
                'longitude' => -3.9260,
                'commune'   => 'Port-Bouët',
            ],
            [
                'name'      => 'Palais des Sports',
                'address'   => 'Treichville, Abidjan',
                'latitude'  => 5.2980,
                'longitude' => -3.9980,
                'commune'   => 'Treichville',
            ],
        ];

        $now = now();

        foreach ($stops as $stop) {
            DB::table('pdp_stops')->updateOrInsert(
                ['name' => $stop['name']],
                array_merge($stop, [
                    'is_active' => true,
                    'status'    => 'APPROVED',
                    'updated_at' => $now,
                    'created_at' => $now,
                ])
            );
        }

        $this->command->info('[OK] ' . count($stops) . ' arrêts PDP créés/mis à jour.');
    }
}
