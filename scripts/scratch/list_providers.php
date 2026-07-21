<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$providers = DB::table('providers')->get(['id', 'first_name', 'mobile', 'status', 'email']);
print_r($providers);
