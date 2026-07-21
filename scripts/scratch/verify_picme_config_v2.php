<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

echo "--- OAuth Clients ---\n";
$clients = DB::table('oauth_clients')->get();
foreach ($clients as $client) {
    echo "ID: " . $client->id . " | Name: " . $client->name . " | Secret: " . $client->secret . "\n";
}

echo "\n--- Password Validation (is '123456' correct?) ---\n";
$pwd = '123456';
$u = DB::table('users')->where('mobile', '01010101')->first();
if ($u) {
    $match = Hash::check($pwd, $u->password);
    echo "User 01010101 password match: " . ($match ? "YES" : "NO") . "\n";
}

$d = DB::table('providers')->where('mobile', '02020202')->first();
if ($d) {
    $match = Hash::check($pwd, $d->password);
    echo "Driver 02020202 password match: " . ($match ? "YES" : "NO") . "\n";
}
