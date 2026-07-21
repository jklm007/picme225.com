<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$types = App\ServiceType::select('id', 'name', 'image')->get();
echo json_encode($types, JSON_PRETTY_PRINT);
