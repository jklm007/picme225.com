<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Provider;
use App\ServiceType;
use App\ProviderService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

$serviceTypeId = 1; // Taxi Vtc
$latitude = 5.3484;
$longitude = -4.0305;
$distance = 50;
$now = Carbon::now();

$st = ServiceType::find($serviceTypeId);
echo "Service: {$st->name} | communal=" . ($st->is_communal ? 'yes' : 'no') . "\n";
echo "allowed_variants: " . json_encode($st->allowed_variants) . "\n\n";

foreach (['prive', 'partage', 'arret_pdp'] as $variant) {
    echo "=== VARIANT: $variant ===\n";
    $col = ['prive' => 'opt_private_ride', 'partage' => 'opt_share_ride', 'arret_pdp' => 'opt_arret_ride'][$variant];

    $q = Provider::with('service')
        ->select(DB::raw("(6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) AS distance"), 'providers.*')
        ->where('status', 'approved')
        ->whereRaw("(6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance")
        ->whereHas('service', function ($query) use ($serviceTypeId) {
            $query->where('status', 'active')->where('service_type_id', $serviceTypeId);
        })
        ->where($col, 1);

    if (in_array($variant, ['partage', 'arret_pdp', 'arret_hybride'])) {
        $q->where('subscription_expires_at', '>', $now)->whereNotNull('subscription_expires_at');
    }

    $providers = $q->orderBy('distance')->get();
    echo "Count after geo+service+variant: " . $providers->count() . "\n";
    foreach ($providers as $p) {
        $afford = $p->canAffordCommission(5000);
        echo "  ID={$p->id} dist=" . round($p->distance, 2) . "km eco={$p->eco_wallet_balance} sub=" . ($p->subscription_expires_at ?? 'null') . " afford=" . ($afford ? 'Y' : 'N') . "\n";
    }
    $filtered = $providers->filter(fn($p) => $p->canAffordCommission(5000));
    echo "After canAffordCommission: " . $filtered->count() . "\n\n";
}

// List all providers on taxi vtc service
echo "=== ALL PROVIDERS ON TAXI VTC (service_type=1) ===\n";
$ps = ProviderService::where('service_type_id', 1)->where('status', 'active')->with('provider')->get();
foreach ($ps as $row) {
    $p = $row->provider;
    if (!$p) continue;
    echo "Provider {$p->id}: opt_private={$p->opt_private_ride} opt_share={$p->opt_share_ride} opt_arret={$p->opt_arret_ride} sub={$p->subscription_expires_at} lat={$p->latitude} lng={$p->longitude} status={$p->status}\n";
}
