<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$listings = \App\Models\MarketplaceListing::withTrashed()->where('title', 'like', '%barbecue%')->get();
$count = 0;
foreach ($listings as $listing) {
    $listing->restore();
    $count++;
    echo "Restored: " . $listing->title . " (ID: " . $listing->id . ")\n";
}
echo "Total restored: " . $count . "\n";
