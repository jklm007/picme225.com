<?php
include 'vendor/autoload.php';
$app = include 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$cs = DB::table('oauth_clients')->get();
echo json_encode($cs, JSON_PRETTY_PRINT);
