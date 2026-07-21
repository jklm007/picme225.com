<?php

require __DIR__.'/bootstrap/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\User;
use App\Provider;

echo "==================================================\n";
echo "       PERMANENT DATABASE BALANCE ALIGNER         \n";
echo "==================================================\n\n";

echo "--- Aligning Users (CFA -> ECO) ---\n";
$users = User::all();
foreach ($users as $u) {
    $realisticEcoLimit = max(0.0, $u->wallet_balance / 1000.0);
    if ($u->eco_token_balance > $realisticEcoLimit || $u->eco_token_balance == 0.0 || $u->eco_token_balance > 1000) {
        $oldBalance = $u->eco_token_balance;
        $u->eco_token_balance = $realisticEcoLimit;
        $u->save();
        echo sprintf(
            "User ID %d (%s): Updated ECO Balance from %s to %s ECO (Aligned with %s CFA)\n",
            $u->id,
            $u->first_name,
            number_format($oldBalance, 2),
            number_format($u->eco_token_balance, 2),
            number_format($u->wallet_balance, 2)
        );
    }
}

echo "\n--- Aligning Drivers (CFA -> ECO) ---\n";
$providers = Provider::all();
foreach ($providers as $p) {
    $realisticEcoLimit = max(0.0, $p->wallet_balance / 1000.0);
    if ($p->eco_wallet_balance > $realisticEcoLimit || $p->eco_wallet_balance == 0.0 || $p->eco_wallet_balance > 1000) {
        $oldBalance = $p->eco_wallet_balance;
        $p->eco_wallet_balance = $realisticEcoLimit;
        $p->save();
        echo sprintf(
            "Driver ID %d (%s): Updated ECO Balance from %s to %s ECO (Aligned with %s CFA)\n",
            $p->id,
            $p->first_name,
            number_format($oldBalance, 2),
            number_format($p->eco_wallet_balance, 2),
            number_format($p->wallet_balance, 2)
        );
    }
}

echo "\n==================================================\n";
echo "🎉 ALL DATABASE BALANCES ALIGNED SUCCESSFULLY!\n";
echo "==================================================\n";
