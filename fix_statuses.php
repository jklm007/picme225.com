<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Fix listing #24 (Hyundai Kona) – set ACTIVE so it appears on marketplace
$listing = \App\Models\MarketplaceListing::find(24);
if ($listing) {
    $listing->status = 'ACTIVE';
    $listing->save();
    echo "Listing #24 updated: status=" . $listing->status . ", category=" . $listing->category . ", sub_category=" . $listing->sub_category . "\n";
} else {
    echo "Listing #24 not found.\n";
}

// Also fix listing #23 (Kia Sportage) if needed
$listing23 = \App\Models\MarketplaceListing::find(23);
if ($listing23 && $listing23->status !== 'ACTIVE') {
    $listing23->status = 'ACTIVE';
    $listing23->save();
    echo "Listing #23 updated to ACTIVE.\n";
} else {
    echo "Listing #23 status: " . ($listing23->status ?? 'not found') . "\n";
}
