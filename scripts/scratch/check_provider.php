<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Provider;

$providers = Provider::all();
foreach ($providers as $p) {
    echo "ID: " . $p->id . " - Provider: " . $p->first_name . " " . $p->last_name . " (Mobile: " . $p->mobile . ", Status: " . $p->status . ")\n";
}
