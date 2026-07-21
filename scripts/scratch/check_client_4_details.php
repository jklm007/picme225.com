<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$client = DB::table('oauth_clients')->where('id', 4)->first();
if ($client) {
    echo "ID: " . $client->id . "\n";
    echo "Secret: " . $client->secret . "\n";
} else {
    echo "Client 4 not found\n";
    $all = DB::table('oauth_clients')->get();
    foreach ($all as $c) {
        echo "ID: " . $c->id . " Name: " . $c->name . "\n";
    }
}
