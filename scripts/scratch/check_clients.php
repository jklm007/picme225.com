<?php
define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- OAUTH CLIENTS ---\n";
$clients = DB::table('oauth_clients')->get();
foreach ($clients as $client) {
    echo "ID: " . $client->id . " | Name: " . $client->name . " | Secret: " . $client->secret . " | PassGrant: " . $client->password_client . " | Personal: " . $client->personal_access_client . "\n";
}

echo "\n--- PERSONAL ACCESS CLIENTS ---\n";
$pacs = DB::table('oauth_personal_access_clients')->get();
foreach ($pacs as $pac) {
    echo "ID: " . $pac->id . " | ClientID: " . $pac->client_id . "\n";
}
