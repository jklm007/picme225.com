<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PdpRoute;
use App\Models\PdpStop;
use App\Models\PdpRouteSegment;
use Illuminate\Support\Facades\DB;

class OfficialRoutesSeeder extends Seeder
{
    public function run()
    {
        DB::beginTransaction();

        try {
            $json = file_get_contents(__DIR__ . '/json_data/complete_official_routes.json');
            $routesData = json_decode($json, true);

            foreach ($routesData as $rData) {
                // Create Route
                $route = PdpRoute::create([
                    'name' => $rData['name'],
                    'description' => $rData['description'],
                    'type' => $rData['type'],
                    'status' => $rData['status']
                ]);

                $stopMap = []; // Maps order -> stop_id

                // Create Stops
                foreach ($rData['stops'] as $sData) {
                    $stop = PdpStop::create([
                        'pdp_route_id' => $route->id,
                        'name' => $sData['name'],
                        'address' => $sData['address'],
                        'latitude' => $sData['latitude'],
                        'longitude' => $sData['longitude'],
                        'order' => $sData['order'],
                        'type' => $sData['type']
                    ]);
                    $stopMap[$sData['order']] = $stop->id;
                }

                // Create Segments
                foreach ($rData['segments'] as $segData) {
                    if (isset($stopMap[$segData['from_stop_order']]) && isset($stopMap[$segData['to_stop_order']])) {
                        PdpRouteSegment::create([
                            'pdp_route_id' => $route->id,
                            'from_stop_id' => $stopMap[$segData['from_stop_order']],
                            'to_stop_id' => $stopMap[$segData['to_stop_order']],
                            'order' => $segData['from_stop_order'], // Simplified order logic
                            'price' => $segData['price'],
                            'distance_km' => $segData['distance_km']
                        ]);
                    }
                }
            }

            DB::commit();
            $this->command->info('Routes importées depuis JSON avec succès!');
        } catch (\Exception $e) {
            DB::rollback();
            $this->command->error($e->getMessage());
        }
    }
}
