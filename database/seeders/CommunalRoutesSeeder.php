<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PdpRoute;
use App\Models\PdpStop;
use Illuminate\Support\Facades\DB;

class CommunalRoutesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Nettoyage : On supprime d'abord les anciens itinéraires communaux pour éviter les doublons
        PdpRoute::where('type', 'COMMUNAL')->delete();

        $communalRoutes = [
            // COCODY
            [
                'name' => 'C1 : Boucle Universitaire (CHU - Riviera 2)',
                'type' => 'COMMUNAL',
                'base_price_per_segment' => 300,
                'stops' => [
                    ['name' => 'CHU de Cocody', 'lat' => 5.3370, 'lng' => -4.0010],
                    ['name' => 'Campus 2000', 'lat' => 5.3400, 'lng' => -3.9960],
                    ['name' => 'Cité Mermoz', 'lat' => 5.3440, 'lng' => -3.9900],
                    ['name' => 'INSAAC', 'lat' => 5.3480, 'lng' => -3.9850],
                    ['name' => 'Carrefour Duncan', 'lat' => 5.3520, 'lng' => -3.9780],
                    ['name' => 'Riviera 2', 'lat' => 5.3550, 'lng' => -3.9700],
                ]
            ],
            [
                'name' => 'C2 : Faya - Cap Nord',
                'type' => 'COMMUNAL',
                'base_price_per_segment' => 500,
                'stops' => [
                    ['name' => 'Carrefour Faya', 'lat' => 5.3700, 'lng' => -3.9500],
                    ['name' => 'Jules Verne', 'lat' => 5.3650, 'lng' => -3.9550],
                    ['name' => 'Carrefour Palmeraie', 'lat' => 5.3600, 'lng' => -3.9600],
                    ['name' => 'Riviera 3 (9 Kilos)', 'lat' => 5.3550, 'lng' => -3.9650],
                    ['name' => 'Cap Nord', 'lat' => 5.3500, 'lng' => -3.9750],
                ]
            ],
            // YOPOUGON
            [
                'name' => 'Y1 : Niangon - Siporex',
                'type' => 'COMMUNAL',
                'base_price_per_segment' => 500,
                'stops' => [
                    ['name' => 'Terminus 27 (Niangon)', 'lat' => 5.3150, 'lng' => -4.0900],
                    ['name' => 'Académie', 'lat' => 5.3200, 'lng' => -4.0850],
                    ['name' => 'Maroc (Antenne)', 'lat' => 5.3250, 'lng' => -4.0800],
                    ['name' => 'Sable', 'lat' => 5.3300, 'lng' => -4.0750],
                    ['name' => 'Siporex', 'lat' => 5.3350, 'lng' => -4.0650],
                ]
            ],
            [
                'name' => 'Y2 : Zone Industrielle - Port-Bouët 2',
                'type' => 'COMMUNAL',
                'base_price_per_segment' => 300,
                'stops' => [
                    ['name' => 'Zone Industrielle', 'lat' => 5.3400, 'lng' => -4.0800],
                    ['name' => 'Carrefour MACA', 'lat' => 5.3380, 'lng' => -4.0850],
                    ['name' => 'BAE', 'lat' => 5.3350, 'lng' => -4.0900],
                    ['name' => 'Toits Rouges', 'lat' => 5.3320, 'lng' => -4.0950],
                    ['name' => 'Port-Bouët 2', 'lat' => 5.3300, 'lng' => -4.1000],
                ]
            ],
            // ABOBO
            [
                'name' => 'A1 : Abobo Baoulé - Mairie',
                'type' => 'COMMUNAL',
                'base_price_per_segment' => 300,
                'stops' => [
                    ['name' => 'Abobo Baoulé', 'lat' => 5.4150, 'lng' => -4.0150],
                    ['name' => 'Carrefour Samaké', 'lat' => 5.4200, 'lng' => -4.0100],
                    ['name' => 'Mairie d\'Abobo', 'lat' => 5.4250, 'lng' => -4.0050],
                    ['name' => 'Gare d\'Abobo', 'lat' => 5.4300, 'lng' => -4.0000],
                ]
            ],
            // MARCORY
            [
                'name' => 'M1 : Boucle de Marcory',
                'type' => 'COMMUNAL',
                'base_price_per_segment' => 300,
                'stops' => [
                    ['name' => 'INJS', 'lat' => 5.3000, 'lng' => -3.9850],
                    ['name' => 'Sicogi', 'lat' => 5.3050, 'lng' => -3.9900],
                    ['name' => 'Marché de Marcory', 'lat' => 5.3100, 'lng' => -3.9950],
                    ['name' => 'Carrefour Solibra', 'lat' => 5.3150, 'lng' => -4.0000],
                    ['name' => 'Zone 4 (Rue PMC)', 'lat' => 5.3050, 'lng' => -4.0050],
                ]
            ],
        ];

        DB::beginTransaction();
        try {
            foreach ($communalRoutes as $routeData) {
                // Créer la ligne
                $route = PdpRoute::create([
                    'name' => $routeData['name'],
                    'type' => $routeData['type'],
                    'status' => 'APPROVED',
                    'is_active' => true,
                    'base_price_per_segment' => $routeData['base_price_per_segment'],
                    'max_detour_communal' => 2, // Détour max 2km pour du communal
                ]);

                // Créer et lier les arrêts
                foreach ($routeData['stops'] as $index => $stopData) {
                    $stop = PdpStop::firstOrCreate(
                        ['name' => $stopData['name']],
                        [
                            'latitude' => $stopData['lat'],
                            'longitude' => $stopData['lng'],
                            'is_active' => true,
                        ]
                    );

                    // Prix pour aller d'un arrêt au suivant = prix FIXE maximisé
                    // Si c'est le dernier arrêt, pas de prix suivant
                    $segmentPrice = ($index < count($routeData['stops']) - 1) ? $routeData['base_price_per_segment'] : 0;

                    $route->stops()->attach($stop->id, [
                        'order' => $index + 1,
                        'price' => $segmentPrice
                    ]);
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            echo "Erreur : " . $e->getMessage() . "\n";
        }
    }
}
