<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$routes = \App\Models\PdpRoute::where('type', 'COMMUNAL')->get();
echo "Starting update for " . count($routes) . " communal routes...\n";

foreach ($routes as $route) {
    echo "Processing Route ID {$route->id} - {$route->name}\n";
    $stops = DB::table('pdp_route_stops')
        ->join('pdp_stops', 'pdp_route_stops.pdp_stop_id', '=', 'pdp_stops.id')
        ->where('pdp_route_stops.pdp_route_id', $route->id)
        ->orderBy('pdp_route_stops.order')
        ->select('pdp_stops.id', 'pdp_stops.name', 'pdp_stops.latitude', 'pdp_stops.longitude', 'pdp_route_stops.order', 'pdp_route_stops.price')
        ->get();

    if (count($stops) < 2) continue;

    DB::table('pdp_route_segments')->where('pdp_route_id', $route->id)->delete();

    for ($i = 0; $i < count($stops) - 1; $i++) {
        $from = $stops[$i];
        $to = $stops[$i+1];
        
        $routing = get_osrm_routing($from->latitude, $from->longitude, $to->latitude, $to->longitude);
        
        $distanceKm = 0;
        if ($routing) {
            $distanceKm = round($routing['distance'] / 1000, 2);
            echo "  Segment {$from->name} -> {$to->name} : OSRM SUCCESS ({$distanceKm} km)\n";
        } else {
            $earthRadius = 6371;
            $latFrom = deg2rad($from->latitude);
            $lonFrom = deg2rad($from->longitude);
            $latTo = deg2rad($to->latitude);
            $lonTo = deg2rad($to->longitude);
            $latDelta = $latTo - $latFrom;
            $lonDelta = $lonTo - $lonFrom;
            $a = sin($latDelta / 2) * sin($latDelta / 2) + cos($latFrom) * cos($latTo) * sin($lonDelta / 2) * sin($lonDelta / 2);
            $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
            $distanceKm = round($earthRadius * $c, 2);
            echo "  Segment {$from->name} -> {$to->name} : OSRM FAILED (fallback {$distanceKm} km)\n";
        }

        DB::table('pdp_route_segments')->insert([
            'pdp_route_id' => $route->id,
            'from_stop_id' => $from->id,
            'to_stop_id' => $to->id,
            'order' => $i + 1,
            'price' => $from->price,
            'distance_km' => $distanceKm,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        usleep(500000);
    }
}
echo "All segments updated successfully!\n";
