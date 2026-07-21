<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\MarketplaceListing;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

$listings = MarketplaceListing::all();
$to_delete = [];
$to_keep = [];

foreach ($listings as $listing) {
    if (empty($listing->cover_image)) {
        $to_delete[] = $listing->id;
        continue;
    }

    $src = $listing->cover_image;
    $exists = false;

    // Check if it's a full URL
    if (str_starts_with($src, 'http')) {
        try {
            $response = Http::timeout(3)->head($src);
            if ($response->successful()) {
                $exists = true;
            }
        } catch (\Exception $e) {
            $exists = false;
        }
    } else {
        // It's a relative path. Check local public disk first
        if (Storage::disk('public')->exists($src)) {
            $exists = true;
        } 
        // Then check S3
        elseif (Storage::disk('s3')->exists($src)) {
            $exists = true;
        }
    }

    if ($exists) {
        $to_keep[] = $listing->id;
    } else {
        $to_delete[] = $listing->id;
    }
}

echo "Total listings: " . count($listings) . "\n";
echo "To Keep: " . count($to_keep) . " -> " . implode(", ", $to_keep) . "\n";
echo "To Delete (broken/no images): " . count($to_delete) . " -> " . implode(", ", $to_delete) . "\n";

// To actually delete:
// MarketplaceListing::whereIn('id', $to_delete)->delete();
