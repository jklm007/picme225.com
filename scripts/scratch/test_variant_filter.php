<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Service;
use App\ServiceType;

echo "=== TEST VARIANT FILTERING ===\n\n";

// 1. Check that Taxi service exists
$taxi = Service::where('name', 'Taxi')->first();
if (!$taxi) {
    echo "ERROR: Service 'Taxi' not found!\n";
    exit(1);
}
echo "OK: Service 'Taxi' found (id={$taxi->id})\n";

// 2. Get ALL service types for Taxi
$allTypes = $taxi->serviceTypes;
echo "\nAll Taxi service types ({$allTypes->count()}):\n";
foreach ($allTypes as $st) {
    $variants = is_array($st->allowed_variants) ? json_encode($st->allowed_variants) : $st->allowed_variants;
    echo "  - [{$st->id}] {$st->name} | allowed_variants={$variants}\n";
}

// 3. Test filtering by each variant
$variants = ['prive', 'dynamique', 'arret'];
foreach ($variants as $variant) {
    $filtered = $taxi->serviceTypes()->whereJsonContains('allowed_variants', $variant)->get();
    echo "\nVariant '{$variant}' => {$filtered->count()} results:\n";
    foreach ($filtered as $st) {
        echo "  - [{$st->id}] {$st->name}\n";
    }
}

echo "\n=== RAW DB CHECK ===\n";
$raw = DB::table('service_types')->select('id','name','allowed_variants')->get();
foreach ($raw as $r) {
    echo "  [{$r->id}] {$r->name} => {$r->allowed_variants}\n";
}

echo "\n=== DONE ===\n";
