<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$serviceTypes = App\Models\ServiceType::all();
foreach($serviceTypes as $st) {
    echo "ID: " . $st->id . " | Name: " . $st->name . " | is_intercommunal: " . $st->is_intercommunal . " | is_shared: " . $st->is_shared . "\n";
}

echo "\n--- PIVOT TABLE service_service_type ---\n";
$pivot = DB::table('service_service_type')->get();
foreach($pivot as $p) {
    echo "Service ID: " . $p->service_id . " | ServiceType ID: " . $p->service_type_id . "\n";
}
