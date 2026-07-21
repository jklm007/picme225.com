<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$u = App\User::where('mobile', '0759747444')->first();
if (!$u) {
    $u = App\User::first();
}
echo "User ID: " . $u->id . "\n";
echo "first_name: " . $u->first_name . "\n";
echo "last_name: " . $u->last_name . "\n";
echo "display_name: [" . $u->display_name . "]\n";
echo "mobile: " . $u->mobile . "\n";
