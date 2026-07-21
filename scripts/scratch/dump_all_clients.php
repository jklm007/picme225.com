<?php
require __DIR__.'/bootstrap/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$clients = DB::table('oauth_clients')->get();
$out = "--- ALL OAUTH CLIENTS ---\n";
foreach ($clients as $client) {
    $out .= "ID: {$client->id} | Name: {$client->name} | Secret: {$client->secret} | Password: {$client->password_client}\n";
}
file_put_contents('all_clients.txt', $out);
echo "Done\n";
