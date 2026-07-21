<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$parents = \App\Models\MarketplaceCategory::whereNull('parent_id')->get();
echo "Parents Count (null): " . count($parents) . "\n";
$parents0 = \App\Models\MarketplaceCategory::where('parent_id', 0)->get();
echo "Parents Count (0): " . count($parents0) . "\n";
$parentsEmpty = \App\Models\MarketplaceCategory::where('parent_id', '')->get();
echo "Parents Count (empty string): " . count($parentsEmpty) . "\n";

foreach(\App\Models\MarketplaceCategory::all() as $c) {
    echo $c->id . " - " . $c->name . " - parent: " . json_encode($c->parent_id) . "\n";
}
