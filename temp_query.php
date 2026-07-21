<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$st = \App\Models\ServiceType::find(1);
if ($st) {
    print_r($st->toArray());
} else {
    echo "ServiceType with ID 1 not found.\n";
}
