<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$c = App\Models\AdCampaign::with('adSlots')->find(1);
echo 'Campaign 1 slots: ';
if ($c) {
    foreach ($c->adSlots as $s) {
        echo $s->name . ', ';
    }
} else {
    echo 'Campaign not found';
}
echo "\nAll Active Slots: ";
foreach (App\Models\AdSlot::where('is_active', true)->get() as $s) {
    echo $s->name . ', ';
}
echo "\n";
