<?php
// Quick DB inspection script
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== pdp_stops columns ===\n";
$cols = DB::select("SHOW COLUMNS FROM pdp_stops");
foreach ($cols as $c) {
    echo $c->Field . " | " . $c->Type . "\n";
}
