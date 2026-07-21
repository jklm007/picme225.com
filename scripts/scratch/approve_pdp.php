<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$updated = \App\PdpStop::where('status', 'PENDING')
    ->update(['status' => 'APPROVED', 'is_public' => 1, 'is_active' => 1]);

echo "Successfully updated $updated PDP stops to APPROVED and PUBLIC.\n";
