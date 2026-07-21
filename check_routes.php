<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$routes = DB::table('pdp_routes')->get();
foreach($routes as $route) {
    echo $route->id . " - " . $route->type . "\n";
}
