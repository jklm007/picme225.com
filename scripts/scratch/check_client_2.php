<?php
require __DIR__.'/bootstrap/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$client = DB::table('oauth_clients')->where('id', 2)->first();
$out = "--- OAUTH CLIENT ID 2 ---\n";
if ($client) {
    $out .= "ID: {$client->id}\n";
    $out .= "Name: {$client->name}\n";
    $out .= "Secret: {$client->secret}\n";
    $out .= "Password Client: {$client->password_client}\n";
} else {
    $out .= "Client ID 2 not found!\n";
}
file_put_contents('client_2_check.txt', $out);
echo "Done\n";
