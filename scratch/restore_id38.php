<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$listing = \App\Models\MarketplaceListing::withTrashed()->find(38);
if ($listing) {
    $listing->restore();
    echo "Restored: " . $listing->title . " (ID: " . $listing->id . ")\n";
    echo "Cover image: " . $listing->cover_image . "\n";
} else {
    echo "Listing not found!\n";
}
