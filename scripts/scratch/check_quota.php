<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$p = App\Provider::find(12);

echo "=== PROVIDER 12 STATE ===\n";
echo "smart_quota_count: " . $p->smart_quota_count . "\n";
echo "smart_last_used_at: " . $p->smart_last_used_at . "\n";
echo "is_smart_mode: " . ($p->is_smart_mode ? 'TRUE' : 'FALSE') . "\n";
echo "smart_mode_type: " . $p->smart_mode_type . "\n";

echo "\n=== FILLABLE CHECK ===\n";
$fillable = $p->getFillable();
$smartFields = ['is_smart_mode', 'smart_mode_type', 'smart_dest_lat', 'smart_dest_lng', 'smart_dest_address', 'smart_zone_radius', 'smart_communes', 'smart_quota_count', 'smart_last_used_at'];
foreach ($smartFields as $field) {
    $inFillable = in_array($field, $fillable);
    echo ($inFillable ? "✅" : "❌ NOT FILLABLE") . " $field\n";
}

echo "\n=== QUOTA LIMIT SIMULATION ===\n";
// Simulate the quota check from update_smart_mode
$today = \Carbon\Carbon::today();
if ($p->smart_last_used_at && !\Carbon\Carbon::parse($p->smart_last_used_at)->isSameDay($today)) {
    echo "→ Different day, quota would be reset to 0\n";
} else {
    echo "→ Same day: current quota = " . $p->smart_quota_count . "/3\n";
}

if ($p->smart_quota_count >= 3) {
    echo "❌ QUOTA LIMIT REACHED! This is why it fails with 403.\n";
} else {
    echo "✅ Quota OK, " . (3 - $p->smart_quota_count) . " uses remaining today\n";
}

echo "\n=== RESET QUOTA FOR TESTING ===\n";
$p->smart_quota_count = 0;
$p->smart_last_used_at = null;
$p->is_smart_mode = false;
$p->save();
echo "✅ Quota reset to 0. Provider ready for fresh test.\n";
