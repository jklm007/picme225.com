<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$route = \App\Models\PdpRoute::with('segments')->where('name', 'like', '%UTB%')->first();
echo json_encode($route->segments, JSON_PRETTY_PRINT);
