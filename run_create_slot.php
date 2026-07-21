<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AdSlot;
$slot = AdSlot::firstOrCreate(['name' => 'POPUP_APP_OPEN'], [
    'description' => 'Pop-up à l\'ouverture de l\'application (1 fois par jour)',
    'width' => 600,
    'height' => 800,
    'ad_format' => 'POPUP',
    'min_cpm' => 2000,
    'min_cpc' => 100
]);
echo "Slot created: " . $slot->id . "\n";
