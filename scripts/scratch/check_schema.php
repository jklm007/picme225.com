<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== PROVIDERS TABLE COLUMNS ===\n";
$cols = DB::select('DESCRIBE providers');
$smartCols = [];
foreach ($cols as $c) {
    if (strpos($c->Field, 'smart') !== false || strpos($c->Field, 'quota') !== false) {
        $smartCols[] = $c->Field;
        echo "✅ " . $c->Field . " | " . $c->Type . " | Null: " . $c->Null . " | Default: " . $c->Default . "\n";
    }
}

echo "\n=== MISSING SMART MODE COLUMNS? ===\n";
$required = ['is_smart_mode', 'smart_mode_type', 'smart_dest_lat', 'smart_dest_lng', 'smart_dest_address', 'smart_zone_radius', 'smart_communes', 'smart_quota_count', 'smart_last_used_at'];
foreach ($required as $col) {
    $found = in_array($col, $smartCols);
    echo ($found ? "✅" : "❌ MISSING") . " $col\n";
}

echo "\n=== TEST: Update Provider ID 12 smart_mode ===\n";
try {
    $provider = App\Provider::find(12);
    if (!$provider) {
        echo "Provider 12 not found!\n";
    } else {
        $provider->is_smart_mode = true;
        $provider->smart_mode_type = 'HOME';
        $provider->smart_dest_lat = 5.36;
        $provider->smart_dest_lng = -4.02;
        $provider->smart_dest_address = 'Test Maison';
        $provider->smart_zone_radius = 5;
        $provider->smart_quota_count = ($provider->smart_quota_count ?? 0) + 1;
        $provider->smart_last_used_at = now();
        $provider->save();
        echo "✅ Provider saved successfully!\n";
        echo "is_smart_mode: " . ($provider->is_smart_mode ? 'TRUE' : 'FALSE') . "\n";
    }
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
