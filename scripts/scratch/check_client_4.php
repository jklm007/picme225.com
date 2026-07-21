<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$client = DB::table('oauth_clients')->where('id', 4)->first();
if ($client) {
    echo "ID: " . $client->id . " | Name: " . $client->name . " | Secret: " . $client->secret . "\n";
} else {
    echo "Client 4 not found!\n";
}
