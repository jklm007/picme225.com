<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$clients = DB::table('oauth_clients')->get();
foreach ($clients as $c) {
    echo "ID: " . $c->id . " | Name: " . $c->name . " | Password: " . $c->password_client . " | Secret: " . $c->secret . "\n";
}
