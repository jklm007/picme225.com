<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$services = \App\Service::all();
echo "SERVICES:\n";
echo json_encode($services, JSON_PRETTY_PRINT) . "\n\n";

$serviceServiceTypes = \DB::table('service_service_types')->get();
echo "SERVICE_SERVICE_TYPES:\n";
echo json_encode($serviceServiceTypes, JSON_PRETTY_PRINT) . "\n";
