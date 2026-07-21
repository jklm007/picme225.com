<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$result1 = DB::select("SHOW COLUMNS FROM service_types LIKE 'calculator'");
print_r($result1);

// Alter the tables to include DISTANCEHOUR
DB::statement("ALTER TABLE service_service_type MODIFY COLUMN calculator ENUM('MIN', 'HOUR', 'DISTANCE', 'DISTANCEMIN', 'DISTANCEDAY', 'DISTANCEHOUR') NOT NULL");
DB::statement("ALTER TABLE service_types MODIFY COLUMN calculator ENUM('MIN', 'HOUR', 'DISTANCE', 'DISTANCEMIN', 'DISTANCEDAY', 'DISTANCEHOUR') NOT NULL");

echo "Tables altered successfully.\n";
