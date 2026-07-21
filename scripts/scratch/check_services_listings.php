<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$listings = DB::table('marketplace_listings')->where('category', 'SERVICES')->get();
echo json_encode($listings);
