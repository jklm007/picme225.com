<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

DB::statement("ALTER TABLE service_service_type MODIFY COLUMN calculator ENUM('MIN', 'HOUR', 'DISTANCE', 'DISTANCEMIN', 'DISTANCEHOUR', 'DAY', 'DISTANCEDAY', 'SHARED') NOT NULL");

$result = DB::select("SHOW COLUMNS FROM service_service_type LIKE 'calculator'");
print_r($result);

echo "service_service_type altered successfully.\n";
