<?php
define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- FLEETS (FULL DETAILS) ---\n";
$fleets = DB::table('fleets')->get();
foreach ($fleets as $f) {
    echo "Nom: " . $f->name . " | Email: " . $f->email . " | Mobile: " . ($f->mobile ?: 'VIDE') . "\n";
}
