<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$routes = \DB::table('pdp_routes')
    ->where('type', 'COMMUNAL')
    ->get();

foreach ($routes as $r) {
    echo "Route: " . $r->name . "\n";
    $stops = \DB::table('pdp_route_stops')
        ->join('pdp_stops', 'pdp_route_stops.pdp_stop_id', '=', 'pdp_stops.id')
        ->where('pdp_route_stops.route_id', $r->id)
        ->orderBy('pdp_route_stops.order')
        ->select('pdp_stops.name', 'pdp_route_stops.order', 'pdp_stops.latitude', 'pdp_stops.longitude')
        ->get();
    foreach ($stops as $s) {
        echo "  Stop " . $s->order . ": " . $s->name . " (" . $s->latitude . ", " . $s->longitude . ")\n";
    }
    echo "--------------------------\n";
}
