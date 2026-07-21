<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\ProviderService;

$ps = ProviderService::where('provider_id', 2)->get();
echo "=== Thomas provider services ===\n";
foreach ($ps as $s) {
    echo "ID: {$s->id} | TypeID: {$s->service_type_id} | Status: {$s->status} | AC: {$s->has_ac}\n";
}
