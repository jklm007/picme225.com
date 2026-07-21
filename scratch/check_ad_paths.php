<?php
require '/app/vendor/autoload.php';
$app = require '/app/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Storage;

// Check all ad_contents with bad (local) image_url
$contents = \App\Models\AdContent::whereNotNull('image_url')
    ->where('image_url', 'not like', 'http%')
    ->get();

echo "=== AD CONTENTS WITH LOCAL PATHS ===\n";
foreach ($contents as $c) {
    echo "Content #{$c->id} (campaign #{$c->ad_campaign_id}): {$c->image_url}\n";
    
    // Check if the file exists in local storage
    $localPath = ltrim($c->image_url, '/');
    $localPath = str_replace('storage/', '', $localPath);
    $existsLocal = Storage::disk('local')->exists('public/' . $localPath) 
                   || file_exists(public_path($c->image_url));
    echo "  Local file exists: " . ($existsLocal ? "YES" : "NO") . "\n";
    
    // Check on S3 - maybe the file was already uploaded under a different path
    $s3Key = ltrim($c->image_url, '/');
    $s3Key = str_replace('storage/', '', $s3Key);
    $existsS3 = Storage::disk('s3')->exists($s3Key);
    echo "  S3 exists (" . $s3Key . "): " . ($existsS3 ? "YES" : "NO") . "\n";
    
    // Try the ads/ path directly
    $fileName = basename($c->image_url);
    $adsS3Key = 'ads/' . $fileName;
    $existsAds = Storage::disk('s3')->exists($adsS3Key);
    echo "  S3 exists (ads/" . $fileName . "): " . ($existsAds ? "YES" : "NO") . "\n";
}
echo "DONE\n";
