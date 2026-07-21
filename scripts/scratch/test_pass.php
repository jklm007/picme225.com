<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$passbooks = DB::table('wallet_passbooks')->where('status', 'CREDITED')->get();
echo json_encode($passbooks);
