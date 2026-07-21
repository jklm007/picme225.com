<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\User;
use App\Provider;

$users = User::all();
echo "=== ALL USERS ===\n";
foreach ($users as $u) {
    echo "ID: {$u->id} | Name: {$u->first_name} | Lat: {$u->latitude} | Lng: {$u->longitude}\n";
}

$providers = Provider::all();
echo "\n=== ALL PROVIDERS ===\n";
foreach ($providers as $p) {
    echo "ID: {$p->id} | Name: {$p->first_name} | Status: {$p->status} | Lat: {$p->latitude} | Lng: {$p->longitude}\n";
}
