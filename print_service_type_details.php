<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ServiceType;

$sts = ServiceType::with('services')->get();
foreach ($sts as $st) {
    echo "ID: {$st->id} | Name: {$st->name} | Type: {$st->type}\n";
    echo "  - Allowed Variants: " . json_encode($st->allowed_variants) . "\n";
    echo "  - Services: ";
    $sNames = [];
    foreach ($st->services as $s) {
        $sNames[] = "{$s->name} (ID: {$s->id})";
    }
    echo implode(', ', $sNames) . "\n\n";
}
