<?php
include 'vendor/autoload.php';
$app = include 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$p = DB::table('providers')->where('id', 1)->first();
echo json_encode($p, JSON_PRETTY_PRINT);
