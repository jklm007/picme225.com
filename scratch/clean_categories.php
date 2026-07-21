<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\MarketplaceCategory;

$all = MarketplaceCategory::all();
echo "Total categories: " . $all->count() . "\n";
foreach ($all as $cat) {
    echo "ID: {$cat->id} | Name: {$cat->name} | Label: {$cat->label}\n";
}

// Group by name to find duplicates
$duplicates = MarketplaceCategory::select('name')
    ->groupBy('name')
    ->havingRaw('COUNT(*) > 1')
    ->get();

foreach ($duplicates as $dup) {
    echo "Removing duplicates for: {$dup->name}\n";
    $ids = MarketplaceCategory::where('name', $dup->name)->orderBy('id', 'desc')->pluck('id')->toArray();
    array_shift($ids); // Keep one
    MarketplaceCategory::whereIn('id', $ids)->delete();
}
