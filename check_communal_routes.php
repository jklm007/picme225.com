<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$routes = \App\Models\PdpRoute::where('type', 'COMMUNAL')->get();
$ids = $routes->pluck('id')->toArray();
echo "Communal Route IDs: " . implode(', ', $ids) . "\n";

$segments = \DB::table('pdp_route_segments')->whereIn('pdp_route_id', $ids)->get();
echo "Segments count: " . count($segments) . "\n";
