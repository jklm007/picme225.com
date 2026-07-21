<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
print_r(DB::table('wallet_passbooks')->orderBy('id', 'desc')->limit(5)->get()->toArray());
