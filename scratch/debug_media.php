<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Default Disk: " . env('FILESYSTEM_DISK') . "\n";
echo "Config FILESYSTEM_DISK: " . config('filesystems.default') . "\n";

$listing = \App\Models\MarketplaceListing::whereNotNull('media')->whereRaw("media != '[]'")->latest()->first();

if ($listing) {
    echo "Found listing ID: " . $listing->id . "\n";
    echo "Raw media column: " . json_encode($listing->getAttributes()['media']) . "\n";
    echo "Processed media array: " . json_encode($listing->media) . "\n";
    echo "Generated media_url array: " . json_encode($listing->media_url) . "\n";
    echo "Generated cover_image: " . $listing->cover_image . "\n";
} else {
    echo "No listing with media found.\n";
}
