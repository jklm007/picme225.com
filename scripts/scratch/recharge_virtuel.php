<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$amount = 100.0; // Recharge de 100 ECO virtuellement

echo "Recharge virtuelle de $amount ECO pour tous les chauffeurs...\n";

$providers = App\Provider::all();
$count = 0;

foreach ($providers as $provider) {
    $provider->eco_wallet_balance += $amount;
    $provider->save();
    echo "✔ Chauffeur #{$provider->id} ( {$provider->first_name} {$provider->last_name} ) rechargé. Nouveau solde : {$provider->eco_wallet_balance} ECO\n";
    $count++;
}

echo "\nTerminé ! $count comptes chauffeurs ont été rechargés avec succès.\n";
