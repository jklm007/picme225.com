<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $clients = DB::table('oauth_clients')->get();
    echo "OAUTH CLIENTS COUNT: " . $clients->count() . "\n";
    foreach ($clients as $c) {
        echo " - ID: {$c->id}, Name: {$c->name}, Secret: {$c->secret}, Password Client: {$c->password_client}\n";
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
