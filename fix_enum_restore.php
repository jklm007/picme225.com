<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$result1 = DB::select("SHOW COLUMNS FROM service_types LIKE 'calculator'");
print_r($result1);

// Restore all enum values for service_types just in case
DB::statement("ALTER TABLE service_types MODIFY COLUMN calculator ENUM('MIN', 'HOUR', 'DISTANCE', 'DISTANCEMIN', 'DISTANCEHOUR', 'DAY', 'DISTANCEDAY', 'SHARED') NULL");

$result2 = DB::select("SHOW COLUMNS FROM service_types LIKE 'calculator'");
print_r($result2);

echo "Tables altered successfully.\n";
