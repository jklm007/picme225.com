<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Provider;
use Carbon\Carbon;

// Ajuster le solde à 100 ECO (100 000 CFA)
// Et définir l'expiration à 90 jours
$expiresAt = Carbon::now()->addDays(90);

Provider::query()->update([
    'eco_wallet_balance' => 100,
    'eco_bonus_expires_at' => $expiresAt
]);

echo "Solde de tous les chauffeurs ajusté à 100 ECO.\n";
echo "Date d'expiration réglée au : " . $expiresAt->toDateTimeString() . " (90 jours).\n";

// Seuil minimal ECO
foreach (['provider_min_eco_balance', 'min_eco_balance'] as $key) {
    \Setting::set($key, 1);
}
\Setting::save();
echo "Seuil minimal ECO mis à 1 ECO.\n";

$providers = Provider::select('id', 'first_name', 'last_name', 'eco_wallet_balance', 'eco_bonus_expires_at')->get();
foreach ($providers as $p) {
    echo "ID: {$p->id} | Name: {$p->first_name} | Balance: {$p->eco_wallet_balance} | Expires: {$p->eco_bonus_expires_at}\n";
}
