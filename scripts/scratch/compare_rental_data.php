<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\KmHourServiceTypePrice;
use App\ServiceTypeRental;

echo "KmHourServiceTypePrice:\n";
print_r(KmHourServiceTypePrice::take(5)->get()->toArray());
echo "ServiceTypeRental:\n";
print_r(ServiceTypeRental::take(5)->get()->toArray());
