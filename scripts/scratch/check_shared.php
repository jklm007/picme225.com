<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$service = App\Service::with('serviceTypes')->where('name', 'Shared Ride')->first();
if ($service) {
    echo "Found Shared Ride service\n";
    foreach ($service->serviceTypes as $type) {
        echo " - " . $type->name . " (ID: " . $type->id . ") Image: " . $type->image . " URL: " . $type->image_url . "\n";
    }
} else {
    echo "Shared Ride service NOT found\n";
}
