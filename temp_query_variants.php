<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$services = App\Models\ServiceType::all();
foreach($services as $st) {
    echo $st->name . ' : ';
    print_r($st->allowed_variants);
    echo "\n";
}
