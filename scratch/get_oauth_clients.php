<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$clients = DB::table('oauth_clients')->get();
foreach ($clients as $client) {
    echo "ID: " . $client->id . " | Name: " . $client->name . " | Secret: " . $client->secret . " | Redirect: " . $client->redirect . " | Password Client: " . ($client->password_client ? 'Yes' : 'No') . "\n";
}
