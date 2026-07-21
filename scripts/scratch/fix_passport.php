<?php
define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

DB::table('oauth_clients')->truncate();
DB::table('oauth_personal_access_clients')->truncate();

// Client for Driver (ID 1)
DB::table('oauth_clients')->insert([
    'id' => 1,
    'name' => 'Picme Driver Password Grant',
    'secret' => 'rzlIaqH3PBAnnpySPwVFSMyffEdgf0gxKdpPjSvu',
    'provider' => 'providers',
    'redirect' => 'http://localhost',
    'personal_access_client' => 0,
    'password_client' => 1,
    'revoked' => 0,
    'created_at' => now(),
    'updated_at' => now(),
]);

// Client for User (ID 8)
DB::table('oauth_clients')->insert([
    'id' => 8,
    'name' => 'Picme User Password Grant',
    'secret' => 'CTb5n5lbFdVZQ2YhKaugQ6LWfZHD9h03M9eFHE8U',
    'provider' => 'users',
    'redirect' => 'http://localhost',
    'personal_access_client' => 0,
    'password_client' => 1,
    'revoked' => 0,
    'created_at' => now(),
    'updated_at' => now(),
]);

// Personal Access Client (ID 9) - For Unified Login
DB::table('oauth_clients')->insert([
    'id' => 9,
    'name' => 'Picme Personal Access Client',
    'secret' => bin2hex(random_bytes(20)),
    'provider' => 'users',
    'redirect' => 'http://localhost',
    'personal_access_client' => 1,
    'password_client' => 0,
    'revoked' => 0,
    'created_at' => now(),
    'updated_at' => now(),
]);

DB::table('oauth_personal_access_clients')->insert([
    'client_id' => 9,
    'created_at' => now(),
    'updated_at' => now(),
]);

echo "Clients matched & Personal Access Client created successfully!\n";
