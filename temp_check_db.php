<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$loc = App\Service::with('serviceTypes')->find(2);
echo "LOCATION (ID 2):\n";
echo json_encode($loc->toArray(), JSON_PRETTY_PRINT) . "\n\n";

$urg = App\Service::with('serviceTypes')->find(5);
echo "URGENCE (ID 5):\n";
echo json_encode($urg->toArray(), JSON_PRETTY_PRINT) . "\n\n";
