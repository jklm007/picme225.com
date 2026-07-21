<?php
require __DIR__.'/bootstrap/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$clients = DB::table('oauth_clients')->get();
$out = "--- OAUTH CLIENTS ---\n";
foreach ($clients as $client) {
    if ($client->id == 2 || $client->id == 5) {
        $out .= "ID: {$client->id}\n";
        $out .= "Name: {$client->name}\n";
        $out .= "Secret: {$client->secret}\n";
        $out .= "Password Client: {$client->password_client}\n";
        $out .= "--------------------\n";
    }
}
file_put_contents('clients_readable.txt', $out);
echo "Done\n";
