<?php

require __DIR__.'/bootstrap/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\User;
use App\Provider;

echo "==================================================\n";
echo "           DATABASE BALANCE CHECKER               \n";
echo "==================================================\n\n";

echo "--- Users Balances (CFA vs ECO) ---\n";
$users = User::take(5)->get();
foreach ($users as $u) {
    // Simulate loading details to trigger dynamic alignment
    $realisticEcoLimit = max(0.0, $u->wallet_balance / 1000.0);
    $status = ($u->eco_token_balance == $realisticEcoLimit) ? "ALIGNED" : "OUT OF SYNC";
    
    echo sprintf(
        "ID: %d | Name: %s %s | Wallet: %s CFA | ECO Balance: %s ECO | Status: %s\n",
        $u->id,
        $u->first_name,
        $u->last_name,
        number_format($u->wallet_balance, 2),
        number_format($u->eco_token_balance, 2),
        $status
    );
}

echo "\n--- Drivers (Providers) Balances (CFA vs ECO) ---\n";
$providers = Provider::take(5)->get();
foreach ($providers as $p) {
    $realisticEcoLimit = max(0.0, $p->wallet_balance / 1000.0);
    $status = ($p->eco_wallet_balance == $realisticEcoLimit) ? "ALIGNED" : "OUT OF SYNC";
    
    echo sprintf(
        "ID: %d | Name: %s %s | Wallet: %s CFA | ECO Balance: %s ECO | Status: %s\n",
        $p->id,
        $p->first_name,
        $p->last_name,
        number_format($p->wallet_balance, 2),
        number_format($p->eco_wallet_balance, 2),
        $status
    );
}

echo "==================================================\n";
