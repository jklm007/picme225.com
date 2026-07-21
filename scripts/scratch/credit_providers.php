<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Provider;

$providers = Provider::all();
echo "=== CREDITING PROVIDERS ===\n";
foreach ($providers as $p) {
    $p->eco_wallet_balance = 10000;
    $p->status = 'approved'; // Ensure they are approved
    $p->save();
    echo "ID: {$p->id} | Name: {$p->first_name} | New Balance: {$p->eco_wallet_balance}\n";
}
echo "All providers credited with 10,000 ECO.\n";
