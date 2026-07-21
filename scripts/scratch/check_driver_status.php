<?php
define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- DRIVER (PROVIDER) DETAILS ---\n";
$p = DB::table('providers')->where('email', 'demo@demo.com')->first();
if ($p) {
    echo "ID: " . $p->id . " | Status: " . $p->status . " | Fleet: " . ($p->fleet_id ?: 'NONE') . "\n";
} else {
    echo "Driver demo@demo.com NOT found.\n";
}

echo "\n--- PROVIDER SERVICES ---\n";
$services = DB::table('provider_services')->where('provider_id', $p->id)->get();
foreach ($services as $s) {
    echo "TypeID: " . $s->service_type_id . " | Status: " . $s->status . "\n";
}
