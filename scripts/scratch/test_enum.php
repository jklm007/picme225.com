<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$columns = Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM marketplace_listings LIKE 'type'");
print_r($columns);
