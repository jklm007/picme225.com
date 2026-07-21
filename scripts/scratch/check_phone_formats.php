<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = App\User::take(10)->get();
foreach ($users as $u) {
    echo "User: " . $u->email . " | Mobile: [" . $u->mobile . "]\n";
}
echo "\n";
$providers = App\Provider::take(10)->get();
foreach ($providers as $p) {
    echo "Provider: " . $p->email . " | Mobile: [" . $p->mobile . "]\n";
}
