<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$routes = App\PdpRoute::with('stops', 'segments')->get();
foreach ($routes as $route) {
    echo "ID: " . $route->id . "\n";
    echo "Name: " . $route->name . "\n";
    echo "Type: " . $route->type . "\n";
    echo "Status: " . $route->status . "\n";
    echo "Stops: " . $route->stops->count() . "\n";
    echo "Segments: " . $route->segments->count() . "\n";
    echo "--------------------------\n";
}
