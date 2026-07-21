<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Commune;

class PdpAbidjanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $communes = [
            [
                'commune' => 'Cocody',
                'code_commune' => 'CODY',
                'latitude_centre' => 5.3854,
                'longitude_centre' => -3.9922,
                // Un polygone approximatif de Cocody pour l'exemple
                'polygone' => 'POLYGON((-4.01 5.34, -3.95 5.34, -3.95 5.42, -4.01 5.42, -4.01 5.34))'
            ],
            [
                'commune' => 'Plateau',
                'code_commune' => 'PLAT',
                'latitude_centre' => 5.3222,
                'longitude_centre' => -4.0194,
                'polygone' => 'POLYGON((-4.03 5.31, -4.01 5.31, -4.01 5.34, -4.03 5.34, -4.03 5.31))'
            ],
            [
                'commune' => 'Yopougon',
                'code_commune' => 'YOPO',
                'latitude_centre' => 5.3344,
                'longitude_centre' => -4.0766,
                'polygone' => 'POLYGON((-4.12 5.30, -4.04 5.30, -4.04 5.40, -4.12 5.40, -4.12 5.30))'
            ],
            [
                'commune' => 'Marcory',
                'code_commune' => 'MARC',
                'latitude_centre' => 5.3022,
                'longitude_centre' => -3.9744,
                'polygone' => 'POLYGON((-4.00 5.28, -3.95 5.28, -3.95 5.32, -4.00 5.32, -4.00 5.28))'
            ],
            [
                'commune' => 'Treichville',
                'code_commune' => 'TREI',
                'latitude_centre' => 5.3013,
                'longitude_centre' => -4.0055,
                'polygone' => 'POLYGON((-4.02 5.28, -3.99 5.28, -3.99 5.31, -4.02 5.31, -4.02 5.28))'
            ],
            [
                'commune' => 'Abobo',
                'code_commune' => 'ABOB',
                'latitude_centre' => 5.4184,
                'longitude_centre' => -4.0163,
                'polygone' => 'POLYGON((-4.06 5.39, -3.98 5.39, -3.98 5.47, -4.06 5.47, -4.06 5.39))'
            ],
            [
                'commune' => 'Koumassi',
                'code_commune' => 'KOUM',
                'latitude_centre' => 5.2936,
                'longitude_centre' => -3.9463,
                'polygone' => 'POLYGON((-3.97 5.27, -3.92 5.27, -3.92 5.31, -3.97 5.31, -3.97 5.27))'
            ],
            [
                'commune' => 'Port-Bouët',
                'code_commune' => 'PORT',
                'latitude_centre' => 5.2536,
                'longitude_centre' => -3.9547,
                'polygone' => 'POLYGON((-3.99 5.23, -3.90 5.23, -3.90 5.28, -3.99 5.28, -3.99 5.23))'
            ],
            [
                'commune' => 'Bingerville',
                'code_commune' => 'BING',
                'latitude_centre' => 5.3562,
                'longitude_centre' => -3.8860,
                'polygone' => 'POLYGON((-3.92 5.33, -3.85 5.33, -3.85 5.38, -3.92 5.38, -3.92 5.33))'
            ],
            [
                'commune' => 'Adjamé',
                'code_commune' => 'ADJA',
                'latitude_centre' => 5.3524,
                'longitude_centre' => -4.0177,
                'polygone' => 'POLYGON((-4.03 5.33, -4.00 5.33, -4.00 5.37, -4.03 5.37, -4.03 5.33))'
            ]
        ];

        foreach ($communes as $data) {
            $commune = Commune::firstOrCreate(
                ['code_commune' => $data['code_commune']],
                [
                    'pays' => 'Côte d\'Ivoire',
                    'ville' => 'Abidjan',
                    'commune' => $data['commune'],
                    'latitude_centre' => $data['latitude_centre'],
                    'longitude_centre' => $data['longitude_centre'],
                    'statut' => 'actif'
                ]
            );

            // Mise à jour du polygone PostGIS (ST_GeomFromText avec SRID 4326)
            DB::connection('pgsql')->statement("
                UPDATE communes 
                SET polygone_zone = ST_GeomFromText('{$data['polygone']}', 4326) 
                WHERE id = {$commune->id}
            ");
        }

        $this->command->info('Les 10 communes principales d\'Abidjan ont été ajoutées avec leurs polygones PostGIS approchés !');
    }
}
