<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Storage;

// Check listing 38 cover image
$listing = \App\Models\MarketplaceListing::withTrashed()->find(38);
echo "=== Listing 38 ===\n";
echo "cover_image: " . $listing->cover_image . "\n";

// Check S3 URL
$url = Storage::disk('s3')->url($listing->cover_image);
echo "S3 URL: " . $url . "\n";

// Try to get a temporary URL
try {
    $temp = Storage::disk('s3')->temporaryUrl($listing->cover_image, now()->addHours(1));
    echo "Temp URL: " . $temp . "\n";
} catch (\Exception $e) {
    echo "Temp URL error: " . $e->getMessage() . "\n";
}

// Check AWS_URL env
echo "AWS_URL: " . env('AWS_URL') . "\n";
echo "AWS_ENDPOINT: " . env('AWS_ENDPOINT') . "\n";
echo "FILESYSTEM_DISK: " . env('FILESYSTEM_DISK') . "\n";
