<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Provider;

$p = Provider::first();
echo "Wallet Balance: " . $p->wallet_balance . "\n";
echo "ECO Balance: " . $p->eco_wallet_balance . "\n";
