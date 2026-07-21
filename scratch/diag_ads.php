<?php
require '/app/vendor/autoload.php';
$app = require '/app/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// 1. Fetch all ad campaigns with their contents
$campaigns = \App\Models\AdCampaign::with('contents')->get();
echo "=== AD CAMPAIGNS ===\n";
foreach ($campaigns as $c) {
    echo "Campaign #{$c->id}: {$c->name}\n";
    foreach ($c->contents as $content) {
        echo "  Content #{$content->id}: type={$content->content_type}\n";
        echo "  image_url=" . ($content->image_url ?? 'NULL') . "\n";
        echo "  video_url=" . ($content->video_url ?? 'NULL') . "\n";
    }
    if ($c->contents->isEmpty()) echo "  (no contents)\n";
}

// 2. Check disk config
echo "\n=== DISK CONFIG ===\n";
echo "FILESYSTEM_DISK=" . env('FILESYSTEM_DISK', 'local') . "\n";
echo "AWS_URL=" . env('AWS_URL', 'N/A') . "\n";
echo "AWS_BUCKET=" . env('AWS_BUCKET', 'N/A') . "\n";

// 3. Test S3 upload + URL generation
echo "\n=== S3 TEST ===\n";
try {
    $disk = 's3';
    $testFile = 'ads/test_' . time() . '.txt';
    \Illuminate\Support\Facades\Storage::disk($disk)->put($testFile, 'test', 'public');
    $url = \Illuminate\Support\Facades\Storage::disk($disk)->url($testFile);
    echo "Generated URL: " . $url . "\n";
    // Clean up
    \Illuminate\Support\Facades\Storage::disk($disk)->delete($testFile);
    echo "S3 OK\n";
} catch (Exception $e) {
    echo "S3 ERROR: " . $e->getMessage() . "\n";
}
