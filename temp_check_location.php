<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$loc = App\Service::with('serviceTypes')->find(3);
echo "LOCATION (ID 3):\n";
echo json_encode($loc->toArray(), JSON_PRETTY_PRINT) . "\n\n";
