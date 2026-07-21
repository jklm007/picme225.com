<?php
include 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
use Illuminate\Support\Facades\Schema;
echo "ActiveSharedRide price_per_seat: " . (Schema::hasColumn('active_shared_rides', 'price_per_seat') ? 'YES' : 'NO') . PHP_EOL;
echo "ActiveSharedRide route_id: " . (Schema::hasColumn('active_shared_rides', 'pdp_route_id') ? 'YES' : 'NO') . PHP_EOL;
