<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- USERS (Email | Mobile) ---\n";
foreach (App\User::latest()->take(10)->get() as $u) {
    echo $u->email . " | " . $u->mobile . "\n";
}

echo "--- PROVIDERS (Email | Mobile) ---\n";
foreach (App\Provider::latest()->take(10)->get() as $p) {
    echo $p->email . " | " . $p->mobile . "\n";
}
