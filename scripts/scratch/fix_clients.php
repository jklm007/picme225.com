<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$app->make(Kernel::class)->bootstrap();

echo "--- CREATION CLIENTS OAUTH ---" . PHP_EOL;

// 1. Client pour PickMe PRO (ID 4)
$clientId4 = 4;
$clientSecret4 = 'FTclBLuKNkFlyarbF9RJLFpoRLiIIGTpKVGgkyan';

$existingClient4 = DB::table('oauth_clients')->where('id', $clientId4)->first();

if ($existingClient4) {
    echo "Client ID 4 existe deja. Mise a jour du secret..." . PHP_EOL;
    DB::table('oauth_clients')->where('id', $clientId4)->update([
        'secret' => $clientSecret4,
        'redirect' => 'http://localhost',
        'personal_access_client' => 0,
        'password_client' => 1,
        'revoked' => 0,
        'updated_at' => Carbon::now()
    ]);
} else {
    echo "Creation du Client ID 4..." . PHP_EOL;
    DB::table('oauth_clients')->insert([
        'id' => $clientId4,
        'name' => 'PickMe PRO Mobile App',
        'secret' => $clientSecret4,
        'redirect' => 'http://localhost',
        'personal_access_client' => 0,
        'password_client' => 1,
        'revoked' => 0,
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now()
    ]);
}

// 2. Client pour PickMe Driver (ID 5)
$clientId5 = 5;
$clientSecret5 = 'rzlIaqH3PBAnnpySPwVFSMyffEdgf0gxKdpPjSvu';

$existingClient5 = DB::table('oauth_clients')->where('id', $clientId5)->first();

if ($existingClient5) {
    echo "Client ID 5 existe deja. Mise a jour du secret..." . PHP_EOL;
    DB::table('oauth_clients')->where('id', $clientId5)->update([
        'secret' => $clientSecret5,
        'redirect' => 'http://localhost',
        'personal_access_client' => 0,
        'password_client' => 1,
        'revoked' => 0,
        'updated_at' => Carbon::now()
    ]);
} else {
    echo "Creation du Client ID 5..." . PHP_EOL;
    DB::table('oauth_clients')->insert([
        'id' => $clientId5,
        'name' => 'PickMe Driver Mobile App',
        'secret' => $clientSecret5,
        'redirect' => 'http://localhost',
        'personal_access_client' => 0,
        'password_client' => 1,
        'revoked' => 0,
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now()
    ]);
}

echo "--- OPERATION TERMINEE ---" . PHP_EOL;
