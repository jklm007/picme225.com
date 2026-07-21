<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\MarketplaceListing;
use Illuminate\Support\Facades\Schema;

// Find our previously created test vehicle listing or any listing
$listing = MarketplaceListing::orderBy('id', 'desc')->first();

if (!$listing) {
    echo "No listings found.\n";
    return;
}

echo "Testing Serialization for Listing ID: " . $listing->id . "\n";
$json = $listing->toArray();

echo "JSON output:\n";
echo json_encode($json, JSON_PRETTY_PRINT);
echo "\n";

// Ensure 'brand', 'location_city', 'with_driver' exist in JSON
$expectedKeys = ['brand', 'model', 'location_city', 'stock_quantity', 'available_actions'];
foreach($expectedKeys as $key) {
    if (array_key_exists($key, $json)) {
        echo "SUCCESS: Key '$key' exists in output JSON.\n";
    } else {
        echo "ERROR: Key '$key' missing from JSON!\n";
    }
}
