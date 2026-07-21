<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$routes = DB::table('pdp_routes')->where('type', 'INTERURBAN')->get();
foreach($routes as $route) {
    echo "ID: " . $route->id . "\n";
    echo "Nom: " . $route->name . "\n";
    echo "Description: " . $route->description . "\n";
    echo "Statut: " . $route->status . "\n";
    echo "---------------------------\n";
}
