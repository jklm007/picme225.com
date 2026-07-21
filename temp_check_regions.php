<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$ids = [10, 11, 9];
foreach ($ids as $id) {
    $st = \App\ServiceType::find($id);
    if ($st) {
        echo 'Service ' . $id . ' regions: ' . $st->regions()->count() . "\n";
    } else {
        echo 'Service ' . $id . " not found\n";
    }
}
