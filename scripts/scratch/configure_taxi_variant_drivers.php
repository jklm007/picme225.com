<?php
/**
 * Configure les chauffeurs Taxi Vtc pour les variantes partage et arret_pdp.
 * Critères : opt_share_ride=1, opt_arret_ride=1, opt_private_ride=1,
 * abonnement actif (premium), eco_wallet suffisant, service actif, géoloc.
 */
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Provider;
use App\ProviderService;
use App\ServiceType;
use Carbon\Carbon;

$TAXI_VTC_ID = ServiceType::where('name', 'like', '%Taxi Vtc%')->value('id')
    ?? ServiceType::where('name', 'like', '%taxi%')->orderBy('id')->value('id')
    ?? 1;

echo "Taxi Vtc service_type_id = $TAXI_VTC_ID\n";

$providerIds = ProviderService::where('service_type_id', $TAXI_VTC_ID)
    ->where('status', 'active')
    ->pluck('provider_id')
    ->unique();

if ($providerIds->isEmpty()) {
    echo "Aucun ProviderService actif pour Taxi Vtc.\n";
    exit(1);
}

$expiresAt = Carbon::now()->addYear();
$updated = 0;

foreach ($providerIds as $pid) {
    $p = Provider::find($pid);
    if (!$p || $p->status !== 'approved') {
        continue;
    }

    $p->opt_private_ride = 1;
    $p->opt_share_ride = 1;
    $p->opt_arret_ride = 1;
    $p->opt_multi_stop = 1;

    if (empty($p->subscription_expires_at) || Carbon::parse($p->subscription_expires_at)->isPast()) {
        $p->subscription_expires_at = $expiresAt;
    }

    if ((float) $p->eco_wallet_balance < 10000) {
        $p->eco_wallet_balance = 50000;
    }

    // Géoloc par défaut (zone test Cocody) si manquante
    if (empty($p->latitude) || empty($p->longitude)) {
        $p->latitude = 5.3450;
        $p->longitude = -4.0240;
    }

    $p->service_type_id = $TAXI_VTC_ID;
    $p->save();
    $updated++;

    echo "OK Provider #{$p->id} ({$p->first_name}): share={$p->opt_share_ride} arret={$p->opt_arret_ride} sub={$p->subscription_expires_at}\n";
}

echo "\nConfigure $updated chauffeur(s) Taxi Vtc pour partage + arret_pdp.\n";
