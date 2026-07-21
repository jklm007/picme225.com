<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$count = \DB::table('pdp_route_segments')->whereIn('route_id', function($q) {
    $q->select('id')->from('pdp_routes')->where('type', 'COMMUNAL');
})->count();

echo "Communal Route Segments: " . $count . "\n";
