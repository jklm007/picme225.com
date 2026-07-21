<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$columns = \Illuminate\Support\Facades\Schema::getColumnListing('mobile_money_transactions');
echo "Columns in mobile_money_transactions: \n";
print_r($columns);

$columns2 = \Illuminate\Support\Facades\Schema::getColumnListing('wallet_passbooks');
echo "\nColumns in wallet_passbooks: \n";
print_r($columns2);
