<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "--- Services (Categories) ---\n";
echo json_encode(DB::table('services')->get(), JSON_PRETTY_PRINT) . "\n";
echo "--- Ride Variants ---\n";
echo json_encode(DB::table('ride_variants')->get(), JSON_PRETTY_PRINT) . "\n";
echo "--- Service Types ---\n";
echo json_encode(DB::table('service_types')->get(), JSON_PRETTY_PRINT) . "\n";
