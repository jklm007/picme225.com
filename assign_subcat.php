<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\MarketplaceCategory;
use App\Models\MarketplaceListing;

echo "Start assigning sub-categories to listings...\n";

// Get all main categories
$mainCategories = MarketplaceCategory::whereNull('parent_id')->get();
$map = [];

foreach ($mainCategories as $main) {
    // get children
    $children = MarketplaceCategory::where('parent_id', $main->id)->get();
    if ($children->count() > 0) {
        $map[$main->name] = $children->pluck('name')->toArray();
    }
}

$listings = MarketplaceListing::all();
$updated = 0;

foreach ($listings as $listing) {
    // If the listing belongs to a main category that has subcategories
    if (isset($map[$listing->category])) {
        // Pick a random subcategory name from that main category
        $subCatName = $map[$listing->category][array_rand($map[$listing->category])];
        
        $listing->category = $subCatName;
        $listing->save();
        $updated++;
    }
}

echo "Assigned subcategories to $updated listings.\n";
