<?php
include 'vendor/autoload.php';
$app = include 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$ps = DB::table('providers')->select('id', 'first_name', 'last_name', 'email', 'mobile', 'status')->get();
echo json_encode($ps, JSON_PRETTY_PRINT);
