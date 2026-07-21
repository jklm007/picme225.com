<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$segments = \DB::table('pdp_route_segments')->whereIn('pdp_route_id', [46, 47, 48, 49, 50, 51])->get();
echo "Found " . count($segments) . " communal segments.\n";
