<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PdpRoutesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Note: Uses DB::table() directly to avoid PdpStop/PdpRoute models' forced pgsql connection.
     */
    public function run(): void
    {
        $lines = [
            [
                "id"    => 1,
                "name"  => "Cocody – Angré",
                "start" => "Pharmacie St Jean de Cocody",
                "stops" => [
                    ["name" => "Carrefour Vie", "fare" => 200],
                    ["name" => "Carrefour MOBILE", "fare" => 300],
                    ["name" => "Carrefour OPERA", "fare" => 400],
                    ["name" => "Carrefour du 22e Arrondissement", "fare" => 500],
                    ["name" => "Carrefour PETRO IVOIRE", "fare" => 700],
                    ["name" => "Carrefour CHATEAU", "fare" => 700],
                ],
                "end" => ["name" => "Carrefour fin goudron", "fare" => 700],
            ],
            [
                "id"    => 2,
                "name"  => "Riviera 2 – Cocody",
                "start" => "Feu tricolore de la Riviera 2",
                "stops" => [
                    ["name" => "Carrefour marché Anono", "fare" => 200],
                ],
                "end" => ["name" => "Feu tricolore Paroisse Saint Jean", "fare" => 500],
            ],
            [
                "id"    => 3,
                "name"  => "Zoo – Attoban",
                "start" => "Feu du Zoo",
                "stops" => [
                    ["name" => "Carrefour Ste Cécile", "fare" => 200],
                    ["name" => "Feu du 30e Arrondissement", "fare" => 400],
                ],
                "end" => ["name" => "Pharmacie St Bernard", "fare" => 500],
            ],
            [
                "id"    => 4,
                "name"  => "Agban – Vallon",
                "start" => "Carrefour 2 Plateaux Agban",
                "stops" => [
                    ["name" => "Station OLA", "fare" => 200],
                ],
                "end" => ["name" => "Ancienne banque NSIA Rue des Jardins", "fare" => 400],
            ],
            [
                "id"    => 5,
                "name"  => "Neuf (9) Kilos – Riviera 3",
                "start" => "Carrefour 9 Kilos",
                "stops" => [
                    ["name" => "Carrefour de la Mosquée", "fare" => 200],
                ],
                "end" => ["name" => "Gare STL", "fare" => 400],
            ],
            [
                "id"    => 6,
                "name"  => "Palmeraie – Angré par le CHU",
                "start" => "Rond-Point SODECI",
                "stops" => [
                    ["name" => "Carrefour St Viateur", "fare" => 300],
                    ["name" => "Carrefour CHU Angré", "fare" => 500],
                ],
                "end" => ["name" => "Fin goudron", "fare" => 600],
            ],
            [
                "id"    => 13,
                "name"  => "Yopougon Sable – Adjamé Renault",
                "start" => "Yopougon Sable",
                "stops" => [
                    ["name" => "Yopougon 1er Pont", "fare" => 300],
                    ["name" => "Attécoubé", "fare" => 500],
                ],
                "end" => ["name" => "Adjamé Renault", "fare" => 600],
            ],
            [
                "id"    => 14,
                "name"  => "Abobo Gendarmerie – Adjamé Liberté",
                "start" => "Abobo Ancienne Gendarmerie",
                "stops" => [
                    ["name" => "Abobo St Joseph", "fare" => 200],
                    ["name" => "Carrefour Agripac", "fare" => 400],
                ],
                "end" => ["name" => "Adjamé Liberté", "fare" => 500],
            ],
            [
                "id"    => 15,
                "name"  => "Koumassi – Plateau",
                "start" => "Grand Carrefour Koumassi",
                "stops" => [
                    ["name" => "Koumassi Remblais", "fare" => 200],
                    ["name" => "Marcory Station", "fare" => 400],
                ],
                "end" => ["name" => "Plateau Centre", "fare" => 600],
            ],
            [
                "id"    => 17,
                "name"  => "Yopougon Maroc – Sideci",
                "start" => "Carrefour Maroc",
                "stops" => [
                    ["name" => "Carrefour Antenne", "fare" => 200],
                ],
                "end" => ["name" => "Mairie Sideci", "fare" => 400],
            ],
            [
                "id"    => 18,
                "name"  => "Abobo Gare – PK 18",
                "start" => "Mairie d'Abobo",
                "stops" => [
                    ["name" => "Gare SOTRA Abobo", "fare" => 200],
                ],
                "end" => ["name" => "Carrefour PK 18", "fare" => 400],
            ],
            [
                "id"    => 19,
                "name"  => "Bingerville – Adjamé",
                "start" => "Gare Woro-Woro Bingerville",
                "stops" => [
                    ["name" => "Hôpital Mère-Enfant", "fare" => 200],
                    ["name" => "Carrefour FHB Bingerville", "fare" => 300],
                    ["name" => "Carrefour 9 Kilos", "fare" => 500],
                    ["name" => "CHU de Cocody", "fare" => 600],
                ],
                "end" => ["name" => "Adjamé Liberté", "fare" => 800],
            ],
            [
                "id"    => 20,
                "name"  => "Port-Bouët – Treichville",
                "start" => "Carrefour Gonzagueville",
                "stops" => [
                    ["name" => "Phare de Port-Bouët", "fare" => 200],
                    ["name" => "Aéroport FHB", "fare" => 300],
                    ["name" => "Grand Carrefour Koumassi", "fare" => 400],
                    ["name" => "Marcory Station", "fare" => 500],
                ],
                "end" => ["name" => "Palais des Sports", "fare" => 600],
            ],
        ];

        $now = now();

        foreach ($lines as $line) {
            // Upsert the route (using DB::table to avoid Eloquent pgsql model)
            DB::table('pdp_routes')->updateOrInsert(
                ['id' => $line['id']],
                [
                    'name'                    => $line['name'],
                    'type'                    => 'COMMUNAL',
                    'status'                  => 'APPROVED',
                    'max_detour_communal'     => 5,
                    'max_detour_intercommunal'=> 10,
                    'is_active'               => true,
                    'updated_at'              => $now,
                    'created_at'              => $now,
                ]
            );

            $allStops = [];
            $order = 1;

            // Départ
            $startStop = $this->findOrCreateStop($line['start'], $now);
            if ($startStop) {
                $allStops[] = ['stop' => $startStop, 'fare' => 0];
                $this->linkStop($line['id'], $startStop->id, $order++);
            }

            // Intermédiaires
            foreach ($line['stops'] as $stopData) {
                $stop = $this->findOrCreateStop($stopData['name'], $now);
                if ($stop) {
                    $allStops[] = ['stop' => $stop, 'fare' => $stopData['fare']];
                    $this->linkStop($line['id'], $stop->id, $order++);
                }
            }

            // Final
            $endStop = $this->findOrCreateStop($line['end']['name'], $now);
            if ($endStop) {
                $allStops[] = ['stop' => $endStop, 'fare' => $line['end']['fare']];
                $this->linkStop($line['id'], $endStop->id, $order++);
            }

            // Segments
            for ($i = 0; $i < count($allStops) - 1; $i++) {
                $from = $allStops[$i];
                $to   = $allStops[$i + 1];

                $basePrice = $to['fare'] - $from['fare'];
                if ($basePrice <= 0) $basePrice = 100;

                $dist = $this->haversine(
                    $from['stop']->latitude, $from['stop']->longitude,
                    $to['stop']->latitude,   $to['stop']->longitude
                );

                DB::table('pdp_route_segments')->updateOrInsert(
                    [
                        'pdp_route_id'   => $line['id'],
                        'from_stop_id'   => $from['stop']->id,
                        'to_stop_id'     => $to['stop']->id,
                        'service_type_id'=> null,
                    ],
                    [
                        'order'       => $i + 1,
                        'price'       => $basePrice,
                        'distance_km' => round($dist, 2),
                        'commune'     => $from['stop']->commune ?? 'Abidjan',
                        'is_active'   => true,
                        'updated_at'  => $now,
                        'created_at'  => $now,
                    ]
                );
            }
        }

        $this->command->info('[OK] ' . count($lines) . ' lignes PDP créées/mises à jour.');
    }

    /**
     * Find stop by name (no suffix like (A), (B)), or create a fallback.
     * Uses DB::table() to avoid pgsql model connection.
     */
    private function findOrCreateStop(string $stopName, $now)
    {
        $name = trim(preg_replace('/\s*\([^)]*\)\s*$/', '', $stopName));

        $stop = DB::table('pdp_stops')->where('name', '=', $name)->first();

        if (!$stop) {
            $this->command->warn("Arrêt introuvable : {$name}. Création d'un arrêt de secours.");
            DB::table('pdp_stops')->insert([
                'name'       => $name,
                'address'    => $name . ', Côte d\'Ivoire',
                'latitude'   => 5.3000,
                'longitude'  => -4.0000,
                'commune'    => 'Abidjan',
                'is_active'  => true,
                'status'     => 'APPROVED',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $stop = DB::table('pdp_stops')->where('name', '=', $name)->first();
        }

        return $stop;
    }

    /**
     * Link a stop to a route via pivot table.
     */
    private function linkStop(int $routeId, int $stopId, int $order): void
    {
        DB::table('pdp_route_stops')->updateOrInsert(
            ['pdp_route_id' => $routeId, 'pdp_stop_id' => $stopId],
            ['order' => $order]
        );
    }

    /**
     * Haversine distance in km.
     */
    private function haversine(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $R = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
