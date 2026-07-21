<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
try {
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    $exists = Schema::hasTable('oauth_clients');
    echo "Table oauth_clients exists: " . ($exists ? 'Yes' : 'No') . "\n";
    if ($exists) {
        $count = DB::table('oauth_clients')->count();
        echo "Count of clients: " . $count . "\n";
        
        $clients = DB::table('oauth_clients')->get();
        foreach ($clients as $client) {
            echo "ID: " . $client->id . " | Name: " . $client->name . " | Secret: " . $client->secret . "\n";
        }
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
