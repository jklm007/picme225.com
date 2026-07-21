<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo App\PdpStop::count() . ' total stops. ' . App\PdpStop::whereNull('pdp_route_id')->count() . ' orphan stops.';
