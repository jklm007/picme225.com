<?php

use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$app->make(Kernel::class)->bootstrap();

echo "--- DEBUT FIX AUTH ---" . PHP_EOL;

// 1. Reset User Password
try {
    $user = \App\User::where('mobile', '0759747444')->first();
    if($user) {
        $user->password = bcrypt('123456');
        $user->save();
        echo "User (0759747444) Updated Successfully." . PHP_EOL;
    } else {
        echo "User (0759747444) NOT FOUND." . PHP_EOL;
    }
} catch (\Exception $e) {
    echo "Error updating user: " . $e->getMessage() . PHP_EOL;
}

// 2. Reset Provider Password
try {
    $provider = \App\Provider::where('mobile', '8465562222')->first();
    if($provider) {
        $provider->password = bcrypt('123456');
        $provider->save();
        echo "Provider (8465562222) Updated Successfully." . PHP_EOL;
    } else {
        echo "Provider (8465562222) NOT FOUND." . PHP_EOL;
    }
} catch (\Exception $e) {
    echo "Error updating provider: " . $e->getMessage() . PHP_EOL;
}

// 3. List Passport Clients
echo "--- PASSPORT CLIENTS ---" . PHP_EOL;
try {
    $clients = \Laravel\Passport\Client::all();
    foreach($clients as $client) {
        echo "Client ID: " . $client->id . PHP_EOL;
        echo "Name: " . $client->name . PHP_EOL;
        echo "Secret: " . $client->secret . PHP_EOL;
        echo "Redirect: " . $client->redirect . PHP_EOL;
        echo "--------------------------" . PHP_EOL;
    }
} catch (\Exception $e) {
    echo "Error listing clients: " . $e->getMessage() . PHP_EOL;
}

echo "--- FIN FIX AUTH ---" . PHP_EOL;
