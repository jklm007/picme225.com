<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$result = \Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM service_service_type LIKE 'calculator'");
print_r($result);
