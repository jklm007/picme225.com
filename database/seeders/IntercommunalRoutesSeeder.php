<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PdpRoute;
use App\Models\PdpStop;
use App\Models\PdpRouteSegment;
use DB;

class IntercommunalRoutesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Nettoyage éventuel ou suppression pour éviter les doublons (optionnel, selon le besoin)
        // Mais nous allons créer proprement s'ils n'existent pas.
        
        $routesData = [
            [
                'name' => 'Ligne 1 : Yopougon ↔ Plateau',
                'description' => 'Axe Ouest-Centre, idéal pour les travailleurs.',
                'stops' => [
                    ['name' => 'Yopougon Siporex', 'commune' => 'Yopougon', 'lat' => 5.334000, 'lng' => -4.067000],
                    ['name' => 'Yopougon Sable', 'commune' => 'Yopougon', 'lat' => 5.336000, 'lng' => -4.055000],
                    ['name' => 'Yopougon Lavage', 'commune' => 'Yopougon', 'lat' => 5.337500, 'lng' => -4.048000],
                    ['name' => 'Gesco / Carena', 'commune' => 'Yopougon', 'lat' => 5.340000, 'lng' => -4.030000],
                    ['name' => 'Plateau Sorbonne', 'commune' => 'Plateau', 'lat' => 5.321000, 'lng' => -4.019000],
                ]
            ],
            [
                'name' => 'Ligne 2 : Abobo ↔ Plateau via Adjamé',
                'description' => 'Axe Nord-Centre très dense.',
                'stops' => [
                    ['name' => 'Abobo Mairie', 'commune' => 'Abobo', 'lat' => 5.416667, 'lng' => -4.016667],
                    ['name' => 'Abobo Samaké', 'commune' => 'Abobo', 'lat' => 5.405000, 'lng' => -4.020000],
                    ['name' => 'Carrefour Duncan', 'commune' => 'Cocody', 'lat' => 5.385000, 'lng' => -4.015000],
                    ['name' => 'Adjamé Liberté', 'commune' => 'Adjamé', 'lat' => 5.355000, 'lng' => -4.025000],
                    ['name' => 'Plateau Indénié', 'commune' => 'Plateau', 'lat' => 5.335000, 'lng' => -4.020000],
                ]
            ],
            [
                'name' => 'Ligne 3 : Cocody Riviera ↔ Plateau',
                'description' => 'Cible la clientèle étudiante et cadre.',
                'stops' => [
                    ['name' => 'Riviera Faya', 'commune' => 'Cocody', 'lat' => 5.365000, 'lng' => -3.955000],
                    ['name' => 'Riviera Palmeraie', 'commune' => 'Cocody', 'lat' => 5.360000, 'lng' => -3.965000],
                    ['name' => 'Riviera 2', 'commune' => 'Cocody', 'lat' => 5.350000, 'lng' => -3.985000],
                    ['name' => 'Saint-Jean', 'commune' => 'Cocody', 'lat' => 5.340000, 'lng' => -4.005000],
                    ['name' => 'Cité Administrative', 'commune' => 'Plateau', 'lat' => 5.325000, 'lng' => -4.015000],
                ]
            ],
            [
                'name' => 'Ligne 4 : Bingerville ↔ Cocody',
                'description' => 'Connecte la banlieue Est à Abidjan.',
                'stops' => [
                    ['name' => 'Bingerville Marché', 'commune' => 'Bingerville', 'lat' => 5.350000, 'lng' => -3.883333],
                    ['name' => 'Carrefour Feh Kessé', 'commune' => 'Bingerville', 'lat' => 5.355000, 'lng' => -3.900000],
                    ['name' => 'Nouveau Camp Akouédo', 'commune' => 'Cocody', 'lat' => 5.360000, 'lng' => -3.925000],
                    ['name' => 'Riviera 3', 'commune' => 'Cocody', 'lat' => 5.362000, 'lng' => -3.945000],
                    ['name' => 'Carrefour Duncan', 'commune' => 'Cocody', 'lat' => 5.385000, 'lng' => -4.015000],
                ]
            ],
            [
                'name' => 'Ligne 5 : Port-Bouët ↔ Plateau',
                'description' => 'Axe Sud-Centre (VGE).',
                'stops' => [
                    ['name' => 'Port-Bouët Phare', 'commune' => 'Port-Bouët', 'lat' => 5.250000, 'lng' => -3.950000],
                    ['name' => 'Koumassi Grand Carrefour', 'commune' => 'Koumassi', 'lat' => 5.295000, 'lng' => -3.965000],
                    ['name' => 'Marcory Orca', 'commune' => 'Marcory', 'lat' => 5.305000, 'lng' => -3.980000],
                    ['name' => 'Treichville Solibra', 'commune' => 'Treichville', 'lat' => 5.310000, 'lng' => -3.995000],
                    ['name' => 'Plateau Gare Sud', 'commune' => 'Plateau', 'lat' => 5.318000, 'lng' => -4.012000],
                ]
            ]
        ];

        DB::beginTransaction();
        try {
            foreach ($routesData as $r) {
                // Créer ou récupérer la ligne intercommunale
                $route = PdpRoute::firstOrCreate(
                    ['name' => $r['name']],
                    [
                        'type' => 'INTER_COMMUNAL',
                        'status' => 'APPROVED',
                        'description' => $r['description'],
                        'base_price_per_segment' => 200,
                        'is_active' => true,
                        'is_intercommunal' => true,
                        'is_communal' => false
                    ]
                );

                $order = 1;
                $previousStopId = null;

                foreach ($r['stops'] as $s) {
                    // Créer ou récupérer l'arrêt
                    $stop = PdpStop::firstOrCreate(
                        ['name' => $s['name'], 'commune' => $s['commune']],
                        [
                            'latitude' => $s['lat'],
                            'longitude' => $s['lng'],
                            'is_active' => true,
                            'max_waiting_time' => 5
                        ]
                    );

                    // Attacher l'arrêt à la route
                    $exists = DB::table('pdp_route_stops')
                        ->where('pdp_route_id', $route->id)
                        ->where('pdp_stop_id', $stop->id)
                        ->exists();

                    if (!$exists) {
                        DB::table('pdp_route_stops')->insert([
                            'pdp_route_id' => $route->id,
                            'pdp_stop_id' => $stop->id,
                            'order' => $order,
                            'price' => 200
                        ]);
                    }

                    // Créer le segment si on a un arrêt précédent
                    if ($previousStopId) {
                        $segmentExists = PdpRouteSegment::where('pdp_route_id', $route->id)
                            ->where('from_stop_id', $previousStopId)
                            ->where('to_stop_id', $stop->id)
                            ->exists();

                        if (!$segmentExists) {
                            // Calcul simple de distance
                            $prevStop = PdpStop::find($previousStopId);
                            $distanceKm = $this->haversine($prevStop->latitude, $prevStop->longitude, $stop->latitude, $stop->longitude);
                            
                            PdpRouteSegment::create([
                                'pdp_route_id' => $route->id,
                                'from_stop_id' => $previousStopId,
                                'to_stop_id' => $stop->id,
                                'order' => $order - 1,
                                'distance_km' => round($distanceKm, 2),
                                'price' => 200,
                                'is_active' => true
                            ]);
                        }
                    }

                    $previousStopId = $stop->id;
                    $order++;
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Erreur lors du seeding : ' . $e->getMessage());
        }
    }

    private function haversine($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        return $earthRadius * $c;
    }
}
