<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$listing = \App\Models\MarketplaceListing::find(20);
echo "cover_image: " . $listing->getRawOriginal('cover_image') . "\n";
echo "media_url: " . $listing->media_url . "\n";
echo "category: " . $listing->category . "\n";
echo "sub_category: " . $listing->sub_category . "\n";
echo "images raw: ";
print_r($listing->getRawOriginal('images'));
echo "\n";

// Check if webp file exists
$path = storage_path('app/public/whatsapp_ads');
echo "Storage path: $path\n";
echo "Listing webp files:\n";
foreach (glob($path . '/*.webp') as $f) {
    echo "  " . basename($f) . " - " . round(filesize($f)/1024, 1) . "KB\n";
}

// Check public storage link
echo "\nPublic symlink exists: " . (is_link(public_path('storage')) ? 'YES' : 'NO') . "\n";
echo "Public storage path: " . public_path('storage') . "\n";
echo "Symlink target: " . (is_link(public_path('storage')) ? readlink(public_path('storage')) : 'N/A') . "\n";
