<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$route = \App\PdpRoute::where('is_active', true)
    ->with([
        'stops' => function($q) {
            $q->where('is_active', true)->orderBy('order');
        },
        'segments' => function($q) {
            $q->where('is_active', true)->orderBy('order');
        }
    ])
    ->first();

if($route) {
    echo json_encode($route->toArray(), JSON_PRETTY_PRINT);
} else {
    echo "No route found.";
}
