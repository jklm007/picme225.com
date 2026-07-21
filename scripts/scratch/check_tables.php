<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = \Illuminate\Support\Facades\DB::select('SHOW TABLES');
foreach($tables as $t) {
    foreach($t as $v) {
        if (stripos($v, 'market') !== false || stripos($v, 'product') !== false || stripos($v, 'item') !== false || stripos($v, 'announce') !== false || stripos($v, 'list') !== false) {
            echo "Found relevant table: " . $v . "\n";
        }
    }
}
