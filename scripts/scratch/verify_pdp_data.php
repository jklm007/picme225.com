<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

use App\PdpRoute;
use App\PdpStop;

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Routes: " . PdpRoute::count() . "\n";
echo "Stops: " . PdpStop::count() . "\n";

foreach (PdpRoute::all() as $route) {
    echo "- Route: {$route->name} (Active: " . ($route->is_active ? 'YES' : 'NO') . ") | Stops: " . $route->stops()->count() . "\n";
}
