<?php
include 'vendor/autoload.php';
$app = include 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$ds = DB::table('documents')->get();
echo json_encode($ds, JSON_PRETTY_PRINT);
