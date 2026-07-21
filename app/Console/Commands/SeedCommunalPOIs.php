<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PdpStop;
use App\Models\PdpRoute;
use App\Models\PdpRouteSegment;
use Illuminate\Support\Str;
use DB;

class SeedCommunalPOIs extends Command
{
    protected $signature = 'pdp:seed-pois';
    protected $description = 'Seed Communal Points of Interest (POIs) as stops and create fluent routes';

    // Approximation of distances (km) between random points since we don't have exact coordinates in this script
    // We'll set coordinates based on commune centers roughly, but offset them slightly.
    
    private $communes = [
        'Cocody' => ['lat' => 5.385, 'lng' => -3.990],
        'Yopougon' => ['lat' => 5.334, 'lng' => -4.067],
        'Marcory' => ['lat' => 5.303, 'lng' => -3.980],
        'Koumassi' => ['lat' => 5.293, 'lng' => -3.955],
        'Plateau' => ['lat' => 5.326, 'lng' => -4.018],
        'Abobo' => ['lat' => 5.416, 'lng' => -4.014],
        'Adjamé' => ['lat' => 5.358, 'lng' => -4.024],
        'Treichville' => ['lat' => 5.306, 'lng' => -4.004],
        'Port-Bouët' => ['lat' => 5.253, 'lng' => -3.961],
        'Attécoubé' => ['lat' => 5.344, 'lng' => -4.037],
    ];

    private $poiData = [
        'Cocody' => [
            'CHU de Cocody' => 'Hôpital',
            'Université Félix Houphouët-Boigny' => 'Ecole',
            'Carrefour de la Vie' => 'Carrefour',
            'Carrefour Duncan' => 'Carrefour',
            'Commissariat 8ème (Cocody Centre)' => 'Commissariat',
            'Commissariat 12ème (Deux Plateaux)' => 'Commissariat',
            'Commissariat 30ème (Attoban)' => 'Commissariat',
            'Commissariat 18ème (Riviera 3)' => 'Commissariat',
            'Commissariat 22ème (Angré)' => 'Commissariat',
            'Commissariat 35ème (Palmeraie)' => 'Commissariat',
            'Carrefour Faya' => 'Carrefour',
            'Carrefour 9 Kilos' => 'Carrefour',
            'Village SOS Abobo-Doumé' => 'Point d\'intérêt',
            'Pharmacie des Allées' => 'Pharmacie',
            'Lycée Classique d\'Abidjan' => 'Ecole',
            'Lycée Sainte Marie' => 'Ecole',
        ],
        'Yopougon' => [
            'CHU de Yopougon' => 'Hôpital',
            'Carrefour Siporex' => 'Carrefour',
            'Carrefour Sable' => 'Carrefour',
            'Place Figayo' => 'Point d\'intérêt',
            'Commissariat 16ème' => 'Commissariat',
            'Commissariat 17ème' => 'Commissariat',
            'Commissariat 19ème' => 'Commissariat',
            'Pharmacie Bel Air' => 'Pharmacie',
            'Pharmacie Keneya' => 'Pharmacie',
            'Lycée Scientifique de Yopougon' => 'Ecole',
            'Institut des Aveugles' => 'Ecole',
            'Carrefour Zone Industrielle' => 'Carrefour',
            'Gare de Yopougon' => 'Gare',
            'Maroc' => 'Carrefour',
        ],
        'Marcory' => [
            'INJS' => 'Ecole',
            'Commissariat 9ème' => 'Commissariat',
            'Cap Sud' => 'Point d\'intérêt',
            'Pharmacie Tiacoh' => 'Pharmacie',
            'Carrefour Grand Carrefour' => 'Carrefour',
            'Carrefour Solibra' => 'Carrefour',
            'Clinique de Marcory' => 'Hôpital',
            'Collège Moderne de Marcory' => 'Ecole',
            'Zone 4C' => 'Point d\'intérêt',
            'Commissariat 26ème (Aliodan)' => 'Commissariat',
        ],
        'Koumassi' => [
            'Grand Carrefour de Koumassi' => 'Carrefour',
            'Commissariat 6ème' => 'Commissariat',
            'Commissariat 20ème' => 'Commissariat',
            'Hôpital Général de Koumassi' => 'Hôpital',
            'Pharmacie Marais' => 'Pharmacie',
            'Place de l\'Espérance' => 'Point d\'intérêt',
            'Lycée Municipal de Koumassi' => 'Ecole',
            'Gare de Koumassi' => 'Gare',
            'Camp Commando' => 'Point d\'intérêt',
        ],
        'Plateau' => [
            'Préfecture de Police' => 'Commissariat',
            'Commissariat 1er' => 'Commissariat',
            'Cathédrale Saint-Paul' => 'Point d\'intérêt',
            'Sorbonne' => 'Point d\'intérêt',
            'Gare Sud SOTRA' => 'Gare',
            'Hôpital Militaire' => 'Hôpital',
            'Lycée Technique d\'Abidjan' => 'Ecole',
            'Cité Administrative' => 'Point d\'intérêt',
            'Pharmacie du Plateau' => 'Pharmacie',
        ],
        'Abobo' => [
            'Mairie d\'Abobo' => 'Point d\'intérêt',
            'Carrefour Samaké' => 'Carrefour',
            'Commissariat 14ème' => 'Commissariat',
            'Commissariat 15ème' => 'Commissariat',
            'Commissariat 21ème' => 'Commissariat',
            'Hôpital Général d\'Abobo' => 'Hôpital',
            'Gare d\'Abobo' => 'Gare',
            'Lycée Municipal d\'Abobo' => 'Ecole',
            'Université Nangui Abrogoua' => 'Ecole',
            'Pharmacie Dokui' => 'Pharmacie',
        ],
        'Adjamé' => [
            'Forum des Marchés' => 'Point d\'intérêt',
            'Renault' => 'Carrefour',
            'Liberté' => 'Carrefour',
            'Commissariat 3ème' => 'Commissariat',
            'Commissariat 7ème' => 'Commissariat',
            'Hôpital Militaire (HMA)' => 'Hôpital',
            'Gare Routière Nord' => 'Gare',
            'Pharmacie des 220 Logements' => 'Pharmacie',
            'Lycée Moderne d\'Adjamé' => 'Ecole',
        ],
        'Treichville' => [
            'CHU de Treichville' => 'Hôpital',
            'Palais des Sports' => 'Point d\'intérêt',
            'Gare de Bassam' => 'Gare',
            'Commissariat 2ème' => 'Commissariat',
            'Commissariat 4ème' => 'Commissariat',
            'Gare de Treichville' => 'Gare',
            'Lycée Moderne de Treichville' => 'Ecole',
            'Pharmacie Arras' => 'Pharmacie',
        ],
        'Port-Bouët' => [
            'Aéroport FHB' => 'Point d\'intérêt',
            'Commissariat 5ème' => 'Commissariat',
            'Hôpital Général de Port-Bouët' => 'Hôpital',
            'Phare de Port-Bouët' => 'Point d\'intérêt',
            'Lycée Moderne de Port-Bouët' => 'Ecole',
            'Carrefour Akwaba' => 'Carrefour',
            'Pharmacie de l\'Aéroport' => 'Pharmacie',
        ],
        'Attécoubé' => [
            'Mairie d\'Attécoubé' => 'Point d\'intérêt',
            'Commissariat 10ème' => 'Commissariat',
            'Marché d\'Attécoubé' => 'Point d\'intérêt',
            'Carrefour Sebroko' => 'Carrefour',
            'Lycée Municipal d\'Attécoubé' => 'Ecole',
            'Pharmacie de la Paix' => 'Pharmacie',
        ],
    ];

    public function handle()
    {
        $this->info("Starting POI seeding process...");

        DB::beginTransaction();
        try {
            // Disable constraints just in case we need to overwrite
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // 1. Create Stops
            $stopsByCommune = [];

            foreach ($this->poiData as $communeName => $pois) {
                $baseLat = $this->communes[$communeName]['lat'];
                $baseLng = $this->communes[$communeName]['lng'];

                $stopsByCommune[$communeName] = [];
                $i = 0;
                foreach ($pois as $poiName => $poiType) {
                    // Create slightly varied coordinates around the base commune center
                    $latOffset = (rand(-20, 20) / 1000);
                    $lngOffset = (rand(-20, 20) / 1000);

                    // Check if stop exists
                    $stop = PdpStop::where('name', $poiName)->where('commune', $communeName)->first();
                    
                    if (!$stop) {
                        $stop = PdpStop::create([
                            'name' => $poiName,
                            'commune' => $communeName,
                            'latitude' => $baseLat + $latOffset,
                            'longitude' => $baseLng + $lngOffset,
                            'is_active' => true,
                        ]);
                        $this->info("Created Stop: $poiName ($communeName)");
                    }

                    $stopsByCommune[$communeName][] = $stop;
                    $i++;
                }
            }

            // 2. Create optimized fluid Routes within each commune using these stops
            foreach ($stopsByCommune as $communeName => $stops) {
                if (count($stops) < 2) continue;

                // Let's create two main lines per commune to connect them logically
                // Line 1: The major loop
                $this->createRouteForCommune($communeName, "Ligne Principale $communeName", $stops);
                
                // Line 2: The Express line (taking only a few stops)
                $expressStops = collect($stops)->random(min(5, count($stops)))->values()->all();
                $this->createRouteForCommune($communeName, "Ligne Express $communeName", $expressStops);
            }

            DB::commit();
            $this->info("POI seeding completed successfully!");
        } catch (\Exception $e) {
            DB::rollback();
            $this->error("Error: " . $e->getMessage());
            $this->error($e->getTraceAsString());
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    private function createRouteForCommune($communeName, $routeName, $stops)
    {
        // Delete if exists
        $existingRoute = PdpRoute::where('name', $routeName)->where('type', 'COMMUNAL')->first();
        if ($existingRoute) {
            PdpRouteSegment::where('route_id', $existingRoute->id)->delete();
            $existingRoute->delete();
        }

        $route = PdpRoute::create([
            'name' => $routeName,
            'type' => 'COMMUNAL',
            'is_active' => true,
        ]);
        $this->info("Created Route: $routeName");

        // Calculate segments
        // We'll create sequential segments A->B, B->C, C->D
        for ($i = 0; $i < count($stops) - 1; $i++) {
            $stopA = $stops[$i];
            $stopB = $stops[$i+1];

            // Haversine formula for distance
            $dist = $this->haversineGreatCircleDistance($stopA->latitude, $stopA->longitude, $stopB->latitude, $stopB->longitude);
            $dist = max(0.5, $dist); // Minimum 0.5 km

            // Price calculation (Woro-woro are usually ~100 to 200 FCFA per small segment, maybe 500 for long)
            $price = ceil($dist * 100 / 50) * 50; // round to nearest 50 FCFA
            $price = max(100, $price); // Min 100 FCFA

            PdpRouteSegment::create([
                'route_id' => $route->id,
                'start_stop_id' => $stopA->id,
                'end_stop_id' => $stopB->id,
                'distance_km' => $dist,
                'price' => $price,
                'duration_minutes' => ceil($dist * 3), // rough estimate
                'order' => $i + 1,
                'is_active' => true,
            ]);
        }
    }

    /**
     * Calculates the great-circle distance between two points, with
     * the Haversine formula.
     */
    private function haversineGreatCircleDistance($latFrom, $lonFrom, $latTo, $lonTo, $earthRadius = 6371)
    {
        $latFrom = deg2rad($latFrom);
        $lonFrom = deg2rad($lonFrom);
        $latTo = deg2rad($latTo);
        $lonTo = deg2rad($lonTo);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        return $angle * $earthRadius;
    }
}
