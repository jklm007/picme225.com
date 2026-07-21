<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$clients = DB::table('oauth_clients')->get();
foreach ($clients as $client) {
    echo "ID: " . $client->id . " | Name: " . $client->name . " | Secret: " . $client->secret . "\n";
}
