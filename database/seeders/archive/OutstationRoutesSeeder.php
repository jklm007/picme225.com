<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PdpRoute;
use App\Models\PdpStop;
use App\Models\PdpRouteSegment;
use App\Models\InterurbanCompany;
use Illuminate\Support\Facades\DB;

class OutstationRoutesSeeder extends Seeder
{
    /**
     * Run the database seeds for Outstation (INTERURBAN) routes.
     */
    public function run()
    {
        $json = file_get_contents(__DIR__ . '/json_data/outstation_full_data.json');
        $routesData = json_decode($json, true);

        foreach ($routesData as $rData) {
            // Find company by name (UTB, AVS, STIF, SBTA, ART Luxury)
            $companyName = $this->guessCompanyName($rData['name']);
            $company = InterurbanCompany::where('name', $companyName)->first();
            $companyId = $company ? $company->id : null;

            DB::beginTransaction();
            try {
                // We use updateOrCreate for routes to be idempotent
                $route = PdpRoute::updateOrCreate(
                    ['name' => $rData['name']],
                    [
                        'interurban_company_id' => $companyId,
                        'description' => $rData['description'],
                        'type' => 'INTERURBAN',
                        'status' => 'APPROVED',
                        'is_active' => true,
                        'base_price_per_segment' => $rData['base_price'] ?? 5000,
                        'max_detour_intercommunal' => 50 // Standard tolerance for outstation
                    ]
                );

                // Instead of deleting all, we update or create stops
                $stopMap = [];
                $activeStopIds = [];

                foreach ($rData['stops'] as $sData) {
                    $stop = PdpStop::updateOrCreate(
                        [
                            'pdp_route_id' => $route->id,
                            'name' => $sData['name']
                        ],
                        [
                            'interurban_company_id' => $companyId,
                            'address' => $sData['address'] ?? ($sData['name'] . ', Côte d\'Ivoire'),
                            'latitude' => $sData['latitude'],
                            'longitude' => $sData['longitude'],
                            'order' => $sData['order'],
                            'type' => $sData['type'] ?? 'gare',
                            'is_active' => true
                        ]
                    );
                    $stopMap[$sData['order']] = $stop->id;
                    $activeStopIds[] = $stop->id;
                }

                // Clean up stops that are no longer in this route definition
                PdpStop::where('pdp_route_id', $route->id)
                    ->whereNotIn('id', $activeStopIds)
                    ->delete();

                // Recreate segments (segments are more transient)
                $route->segments()->delete();

                foreach ($rData['segments'] as $segData) {
                    if (isset($stopMap[$segData['from_stop_order']]) && isset($stopMap[$segData['to_stop_order']])) {
                        PdpRouteSegment::create([
                            'pdp_route_id' => $route->id,
                            'from_stop_id' => $stopMap[$segData['from_stop_order']],
                            'to_stop_id' => $stopMap[$segData['to_stop_order']],
                            'order' => $segData['from_stop_order'],
                            'price' => $segData['price'],
                            'distance_km' => $segData['distance_km'],
                            'is_active' => true
                        ]);
                    }
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $this->command->error("Error seeding {$rData['name']}: {$e->getMessage()}");
            }
        }

        $this->command->info("VIP Outstation routes seeded successfully!");
    }

    /**
     * Helper to guess company from route name
     */
    private function guessCompanyName($name) {
        $name = strtoupper($name);
        if (strpos($name, 'UTB') !== false) return 'UTB';
        if (strpos($name, 'AVS') !== false) return 'AVS';
        if (strpos($name, 'STIF') !== false) return 'STIF';
        if (strpos($name, 'SBTA') !== false) return 'SBTA';
        if (strpos($name, 'ART LUXURY') !== false) return 'ART Luxury';
        return 'Inconnue';
    }
}
