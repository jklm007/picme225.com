<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$listings = \App\Models\MarketplaceListing::orderBy('id', 'desc')->get();
$duplicates = [];
$seen = [];

foreach ($listings as $listing) {
    $key = strtolower(trim($listing->title)) . '_' . $listing->user_id;
    if (isset($seen[$key])) {
        $duplicates[] = $listing->id;
    } else {
        $seen[$key] = $listing->id;
    }
}

echo "Found " . count($duplicates) . " duplicate listings.\n";
if (count($duplicates) > 0) {
    \App\Models\MarketplaceListing::whereIn('id', $duplicates)->forceDelete();
    echo "Deleted duplicates IDs: " . implode(', ', $duplicates) . "\n";
} else {
    echo "No duplicates found.\n";
}
