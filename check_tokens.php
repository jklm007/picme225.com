<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$u1 = App\Models\User::find(1);
$u10 = App\Models\User::find(10);

echo "User 1 ({$u1->first_name}): token=" . substr($u1->device_token ?? 'NULL', 0, 40) . "...\n";
echo "User 10 ({$u10->first_name}): token=" . substr($u10->device_token ?? 'NULL', 0, 40) . "...\n";
echo "Same token: " . (($u1->device_token === $u10->device_token) ? 'YES (PROBLEM!)' : 'NO (OK)') . "\n";
echo "User 1 token empty: " . (empty($u1->device_token) ? 'YES (PROBLEM!)' : 'NO') . "\n";
echo "User 10 token empty: " . (empty($u10->device_token) ? 'YES (PROBLEM!)' : 'NO') . "\n";
