<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$count = DB::table('pdp_stops')->count();
echo "PDP Stops count: $count\n";

$start = microtime(true);
$s_lat = 5.324; $s_lng = -4.020;
$nearStart = \App\PdpStop::whereRaw("(1.609344 * 3956 * acos( cos( radians('$s_lat') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$s_lng') ) + sin( radians('$s_lat') ) * sin( radians(latitude) ) ) ) <= 5.0")
    ->selectRaw("commune, (1.609344 * 3956 * acos( cos( radians('$s_lat') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$s_lng') ) + sin( radians('$s_lat') ) * sin( radians(latitude) ) ) ) as distance")
    ->orderBy('distance')->first();
$end = microtime(true);
echo "Query took: " . ($end - $start) . " seconds\n";
