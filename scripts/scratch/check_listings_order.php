<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$listings = DB::table('marketplace_listings')->orderBy('created_at', 'desc')->get(['id', 'category', 'created_at']);
echo json_encode($listings);
