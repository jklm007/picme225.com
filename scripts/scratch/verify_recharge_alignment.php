<?php

require __DIR__.'/bootstrap/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\User;
use App\Http\Controllers\WalletController;
use Illuminate\Http\Request;

echo "==================================================\n";
echo "        VERIFY RECHARGE ALIGNMENT LOGIC           \n";
echo "==================================================\n\n";

$user = User::find(1);
echo sprintf(
    "Before Recharge -> Wallet: %s CFA | ECO Balance: %s ECO\n",
    number_format($user->wallet_balance, 2),
    number_format($user->eco_token_balance, 2)
);

// We authenticate the user for the simulation
Auth::login($user);

$controller = new WalletController();
$request = Request::create('/api/wallet/add', 'POST', [
    'amount' => 5000,
    'payment_mode' => 'MOBILE_MONEY'
]);

$response = $controller->add_money($request);
$user->refresh();

echo sprintf(
    "After Recharge  -> Wallet: %s CFA | ECO Balance: %s ECO\n",
    number_format($user->wallet_balance, 2),
    number_format($user->eco_token_balance, 2)
);

// Expected ECO balance: wallet_balance / 1000.0
$expectedEco = $user->wallet_balance / 1000.0;
if (abs($user->eco_token_balance - $expectedEco) < 0.0001) {
    echo "\n✅ SUCCESS: CFA and ECO balances are perfectly aligned in real-time after recharge!\n";
} else {
    echo "\n❌ FAIL: Discrepancy detected between CFA and ECO balances after recharge.\n";
}

echo "==================================================\n";
