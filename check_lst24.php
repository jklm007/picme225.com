<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$listing = \App\Models\MarketplaceListing::find(24);
if ($listing) {
    echo "Status: " . $listing->status . "\n";
    echo "Cat: " . $listing->category . "\n";
    echo "SubCat: " . $listing->sub_category . "\n";
    echo "Image 0: " . substr(json_encode($listing->images), 0, 100) . "...\n";
    echo "Cover Image: " . substr($listing->cover_image, 0, 100) . "...\n";
} else {
    echo "Listing not found.\n";
}
