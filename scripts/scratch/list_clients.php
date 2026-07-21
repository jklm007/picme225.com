<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$clients = DB::table('oauth_clients')->get();
foreach ($clients as $client) {
    echo "ID: {$client->id} | Name: {$client->name} | Secret: {$client->secret} | Password Client: {$client->password_client}\n";
}
