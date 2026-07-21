<?php

namespace Database\Seeders;

use App\Models\PdpStop;
use App\Models\PdpRoute;
use App\Models\PdpRouteSegment;
use Illuminate\Database\Seeder;
use App\Helpers\Helper;

class UnifiedPdpSeeder extends Seeder
{
    /**
     * SEEDER UNIFIÉ - Coordonnées GPS réelles + Calcul automatique des prix
     * 
     * Amélioration : Un seul seeder qui gère tout
     * - Arrêts généraux (pour affichage dans l'app)
     * - Routes avec leurs arrêts
     * - Calcul automatique des distances et prix basés sur GPS
     */
    public function run(): void
    {
        // ========================================
        // ÉTAPE 0 : NETTOYAGE COMPLET (RAZ)
        // ========================================
        \if (\DB::getDriverName() === 'mysql') { \if (\DB::getDriverName() === 'mysql') { \DB::statement('SET FOREIGN_KEY_CHECKS=0;'); } elseif (\DB::getDriverName() === 'pgsql') { \DB::statement("SET session_replication_role = 'replica';"); } } elseif (\DB::getDriverName() === 'pgsql') { \DB::statement("SET session_replication_role = 'replica';"); }
        \DB::table('pdp_route_segments')->delete();
        \DB::table('pdp_stops')->delete();
        \DB::table('pdp_routes')->delete();
        \if (\DB::getDriverName() === 'mysql') { \if (\DB::getDriverName() === 'mysql') { \DB::statement('SET FOREIGN_KEY_CHECKS=1;'); } elseif (\DB::getDriverName() === 'pgsql') { \DB::statement("SET session_replication_role = 'origin';"); } } elseif (\DB::getDriverName() === 'pgsql') { \DB::statement("SET session_replication_role = 'origin';"); }

        // ========================================
        // ÉTAPE 1 : DÉFINIR LES ARRÊTS AVEC COORDONNÉES GPS RÉELLES
        // ========================================

        $stopsData = [
            // *** COCODY - Coordonnées vérifiées Google Maps ***
            [
                'name' => 'Carrefour 9 Kilos',
                'address' => 'Bd François Mitterrand, Cocody, Abidjan',
                'latitude' => 5.3577,
                'longitude' => -3.9645,
                'commune' => 'Cocody',
                'is_recommended' => true,
                'priority' => 20,
            ],
            [
                'name' => 'Rond-Point Riviera Palmeraie',
                'address' => 'Boulevard Mitterrand, Riviera Palmeraie, Cocody',
                'latitude' => 5.3615,
                'longitude' => -3.9615,
                'commune' => 'Cocody',
                'is_recommended' => true,
                'priority' => 18,
            ],
            [
                'name' => 'Pharmacie St Jean',
                'address' => 'Boulevard Latrille, Cocody',
                'latitude' => 5.3364,
                'longitude' => -4.0089,
                'commune' => 'Cocody',
                'is_recommended' => true,
                'priority' => 15,
            ],
            [
                'name' => 'CHU Cocody',
                'address' => 'CHU Cocody, Abidjan',
                'latitude' => 5.3380,
                'longitude' => -3.9990,
                'commune' => 'Cocody',
                'is_recommended' => true,
                'priority' => 25,
            ],
            [
                'name' => 'Carrefour Angré',
                'address' => 'Angré, Cocody',
                'latitude' => 5.3710,
                'longitude' => -3.9620,
                'commune' => 'Cocody',
                'is_recommended' => true,
                'priority' => 16,
            ],
            [
                'name' => 'Riviera 2',
                'address' => 'Riviera 2, Cocody',
                'latitude' => 5.3460,
                'longitude' => -3.9820,
                'commune' => 'Cocody',
                'is_recommended' => true,
                'priority' => 14,
            ],
            [
                'name' => '2 Plateaux',
                'address' => '2 Plateaux, Cocody',
                'latitude' => 5.3420,
                'longitude' => -4.0200,
                'commune' => 'Cocody',
                'is_recommended' => true,
                'priority' => 17,
            ],
            [
                'name' => 'Carrefour Vie',
                'address' => 'Carrefour Vie, Cocody',
                'latitude' => 5.3380,
                'longitude' => -3.9980,
                'commune' => 'Cocody',
                'is_recommended' => false,
                'priority' => 10,
            ],
            [
                'name' => 'Gare STL',
                'address' => 'Gare STL, Cocody',
                'latitude' => 5.3630,
                'longitude' => -3.9640,
                'commune' => 'Cocody',
                'is_recommended' => false,
                'priority' => 12,
            ],
            [
                'name' => 'Carrefour de la Mosquée',
                'address' => 'Carrefour Mosquée, Cocody',
                'latitude' => 5.3600,
                'longitude' => -3.9680,
                'commune' => 'Cocody',
                'is_recommended' => false,
                'priority' => 11,
            ],

            // *** AUTRES COMMUNES ***
            [
                'name' => 'Gare de Yopougon',
                'address' => 'Yopougon, Abidjan',
                'latitude' => 5.345324,
                'longitude' => -4.014575,
                'commune' => 'Yopougon',
                'is_recommended' => true,
                'priority' => 10,
            ],
            [
                'name' => 'Adjamé Liberté',
                'address' => 'Adjamé, Abidjan',
                'latitude' => 5.36012,
                'longitude' => -4.02245,
                'commune' => 'Adjamé',
                'is_recommended' => true,
                'priority' => 20,
            ],
            [
                'name' => 'Plateau Centre',
                'address' => 'Plateau, Abidjan',
                'latitude' => 5.3234,
                'longitude' => -4.0264,
                'commune' => 'Plateau',
                'is_recommended' => true,
                'priority' => 15,
            ],
        ];

        // Créer les arrêts généraux (sans pdp_route_id)
        $createdStops = [];
        foreach ($stopsData as $stopData) {
            $stop = PdpStop::updateOrCreate(
                ['name' => $stopData['name']],
                array_merge($stopData, [
                    'is_active' => true,
                    'max_waiting_time' => 5,
                    'allowed_service_types' => ['Woroworo', 'Taxi Communal'],
                ])
            );
            $createdStops[$stopData['name']] = $stop;
        }

        // ========================================
        // ÉTAPE 2 : DÉFINIR LES ROUTES
        // ========================================

        $routesData = [
            [
                'name' => 'Cocody Express',
                'description' => 'Ligne rapide Pharmacie St Jean - Angré',
                'type' => 'COMMUNAL',
                'max_detour_communal' => 2.0, // 2 km max
                'max_detour_intercommunal' => 5.0,
                'base_price_per_km' => 150, // 150 FCFA/km pour calcul dynamique
                'stops_sequence' => [
                    'Pharmacie St Jean',
                    'Carrefour Vie',
                    'Riviera 2',
                    'Carrefour 9 Kilos',
                    'Carrefour Angré',
                ],
            ],
            [
                'name' => '9 Kilos - Gare STL',
                'description' => 'Liaison 9 Kilos vers Gare STL',
                'type' => 'COMMUNAL',
                'max_detour_communal' => 2.0,
                'max_detour_intercommunal' => 5.0,
                'base_price_per_km' => 150,
                'stops_sequence' => [
                    'Carrefour 9 Kilos',
                    'Carrefour de la Mosquée',
                    'Gare STL',
                ],
            ],
            [
                'name' => 'Palmeraie - Angré',
                'description' => 'Rond-Point Riviera Palmeraie vers Angré',
                'type' => 'COMMUNAL',
                'max_detour_communal' => 2.0,
                'max_detour_intercommunal' => 5.0,
                'base_price_per_km' => 150,
                'stops_sequence' => [
                    'Rond-Point Riviera Palmeraie',
                    'Carrefour Angré',
                ],
            ],
        ];

        // ========================================
        // ÉTAPE 3 : CRÉER LES ROUTES ET SEGMENTS AVEC CALCUL AUTO
        // ========================================

        foreach ($routesData as $routeData) {
            // Créer la route
            $route = PdpRoute::updateOrCreate(
                ['name' => $routeData['name']],
                [
                    'description' => $routeData['description'],
                    'type' => $routeData['type'],
                    'max_detour_communal' => $routeData['max_detour_communal'],
                    'max_detour_intercommunal' => $routeData['max_detour_intercommunal'],
                    'is_active' => true,
                ]
            );

            // D'abord créer tous les arrêts de la route pour avoir leurs IDs
            $createdRouteStops = [];
            $stopsSequence = $routeData['stops_sequence'];
            $order = 1;

            foreach ($stopsSequence as $stopName) {
                $generalStop = $createdStops[$stopName];

                $routeStop = PdpStop::updateOrCreate(
                    [
                        'name' => $stopName,
                        'pdp_route_id' => $route->id,
                    ],
                    [
                        'address' => $generalStop->address,
                        'latitude' => $generalStop->latitude,
                        'longitude' => $generalStop->longitude,
                        'commune' => $generalStop->commune,
                        'order' => $order,
                        'is_active' => true,
                        'max_waiting_time' => 5,
                        'allowed_service_types' => ['Woroworo', 'Taxi Communal'],
                    ]
                );
                $createdRouteStops[] = $routeStop;
                $order++;
            }

            // Ensuite créer les segments reliant ces arrêts
            // On itère jusqu'à l'avant-dernier arrêt car le dernier n'a pas de segment sortant
            for ($i = 0; $i < count($createdRouteStops) - 1; $i++) {
                $fromStop = $createdRouteStops[$i];
                $toStop = $createdRouteStops[$i + 1];

                // CALCUL AUTOMATIQUE DE LA DISTANCE GPS
                $distanceMeters = Helper::haversineGreatCircleDistance(
                    $fromStop->latitude,
                    $fromStop->longitude,
                    $toStop->latitude,
                    $toStop->longitude
                );
                $distanceKm = $distanceMeters / 1000;

                // CALCUL AUTOMATIQUE DU PRIX
                $calculatedPrice = $distanceKm * $routeData['base_price_per_km'];
                $price = ceil($calculatedPrice / 50) * 50;
                $price = max(200, $price);

                PdpRouteSegment::updateOrCreate(
                    [
                        'pdp_route_id' => $route->id,
                        'order' => $fromStop->order,
                    ],
                    [
                        'from_stop_id' => $fromStop->id,
                        'to_stop_id' => $toStop->id,
                        'distance_km' => round($distanceKm, 2),
                        'price' => $price,
                        'commune' => $fromStop->commune,
                        'is_active' => true,
                    ]
                );
            }
        }

        $this->command->info('✅ Seeder unifié terminé avec succès !');
        $this->command->info('📍 ' . count($createdStops) . ' arrêts généraux créés');
        $this->command->info('🚌 ' . count($routesData) . ' routes créées avec calcul automatique des prix');
    }
}
