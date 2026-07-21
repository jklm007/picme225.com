<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$q = DB::select('SHOW COLUMNS FROM active_shared_rides WHERE Field = "pdp_route_id"');
print_r($q);
