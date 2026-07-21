<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$cols = DB::select('DESCRIBE eco_token_transactions');
foreach($cols as $col) {
    echo "{$col->Field}: {$col->Type}\n";
}
