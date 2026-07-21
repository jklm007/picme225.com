<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "--- OAuth Clients ---\n";
$clients = DB::table('oauth_clients')->whereIn('id', [4, 5])->get();
foreach ($clients as $client) {
    echo "ID: " . $client->id . " | Secret: " . $client->secret . "\n";
}

echo "\n--- Test User (ID 10/11) ---\n";
$users = DB::table('users')->where('email', 'test@demo.com')->orWhere('mobile', '01010101')->get();
foreach ($users as $u) {
    echo "ID: " . $u->id . " | Email: " . $u->email . " | Mobile: " . $u->mobile . " | Password (hashed): " . $u->password . "\n";
}

echo "\n--- Test Driver (Provider) ---\n";
$drivers = DB::table('providers')->where('email', 'driver@demo.com')->orWhere('mobile', '02020202')->get();
foreach ($drivers as $d) {
    echo "ID: " . $d->id . " | Email: " . $d->email . " | Mobile: " . $d->mobile . " | Status: " . $d->status . " | Password (hashed): " . $d->password . "\n";
}
