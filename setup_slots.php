<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// 1. Create WEB_POPUP and APP_POPUP slots if not exists
$webPopup = App\Models\AdSlot::firstOrCreate(
    ['name' => 'WEB_POPUP'],
    [
        'description' => 'Popup on the Website',
        'is_active' => true,
        'admob_unit_id' => 'ca-app-pub-3940256099942544/6300978111' // AdMob fallback
    ]
);

$appPopup = App\Models\AdSlot::firstOrCreate(
    ['name' => 'APP_POPUP'],
    [
        'description' => 'Popup on the Mobile App',
        'is_active' => true,
        'admob_unit_id' => 'ca-app-pub-3940256099942544/1033173712' // Interstitial test ID
    ]
);

$homeBanner = App\Models\AdSlot::where('name', 'HOME_BANNER')->first();

// 2. Link Campaign 1 to these slots
$c = App\Models\AdCampaign::find(1);
if ($c) {
    // Sync slots to the campaign (HOME_BANNER, WEB_POPUP, APP_POPUP)
    $slotIds = array_filter([$homeBanner->id ?? null, $webPopup->id, $appPopup->id]);
    $c->adSlots()->syncWithoutDetaching($slotIds);
    echo "Campaign 1 successfully linked to slots.\n";
} else {
    echo "Campaign 1 not found.\n";
}
