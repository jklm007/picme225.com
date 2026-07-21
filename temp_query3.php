<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$st10 = App\Models\ServiceType::find(10);
$st11 = App\Models\ServiceType::find(11);

echo "ID 10: "; print_r($st10->allowed_variants); echo "\n";
echo "ID 11: "; print_r($st11->allowed_variants); echo "\n";
