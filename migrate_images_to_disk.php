<?php
/**
 * migrate_images_to_disk.php
 * Script one-shot: migre les images base64 existantes en DB vers des fichiers WebP sur disk.
 * A exécuter UNE SEULE FOIS sur le pod Laravel.
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$storageDisk = \Illuminate\Support\Facades\Storage::disk('public');

// Ensure directory exists
$storageDisk->makeDirectory('marketplace');

$listings = DB::table('marketplace_listings')
    ->whereNotNull('cover_image')
    ->where(function($q) {
        $q->where('cover_image', 'like', 'data:image/%')
          ->orWhere('images', 'like', '%data:image/%');
    })
    ->whereNull('deleted_at')
    ->get();

echo "Found " . count($listings) . " listings with base64 images to migrate.\n\n";

$success = 0; $failed = 0; $skipped = 0;

foreach ($listings as $listing) {
    try {
        $updates = [];

        // Migrate cover_image
        if ($listing->cover_image && str_starts_with($listing->cover_image, 'data:image/')) {
            $path = migrateBase64Image($listing->cover_image, $storageDisk);
            if ($path) {
                $updates['cover_image'] = $path;
                echo "  [OK] Listing #{$listing->id} cover_image -> {$path}\n";
            } else {
                echo "  [FAIL] Listing #{$listing->id} cover_image conversion failed.\n";
                $failed++;
            }
        }

        // Migrate images array
        if ($listing->images) {
            $imagesArray = json_decode($listing->images, true) ?: [];
            $newImages = [];
            foreach ($imagesArray as $img) {
                if (str_starts_with($img, 'data:image/')) {
                    $path = migrateBase64Image($img, $storageDisk);
                    $newImages[] = $path ?: $img; // keep base64 if conversion fails
                    if ($path) echo "  [OK] Listing #{$listing->id} image -> {$path}\n";
                } else {
                    $newImages[] = $img; // already a path
                    $skipped++;
                }
            }
            $updates['images'] = json_encode($newImages);
        }

        if (!empty($updates)) {
            DB::table('marketplace_listings')->where('id', $listing->id)->update($updates);
            $success++;
        }

    } catch (Exception $e) {
        echo "  [ERROR] Listing #{$listing->id}: " . $e->getMessage() . "\n";
        $failed++;
    }
}

echo "\n=== Migration terminée ===\n";
echo "  Réussis  : {$success}\n";
echo "  Ignorés  : {$skipped}\n";
echo "  Échoués  : {$failed}\n";

function migrateBase64Image(string $base64, $disk): ?string
{
    if (preg_match('/^data:image\/(\w+);base64,/', $base64)) {
        $raw = substr($base64, strpos($base64, ',') + 1);
        $imageData = base64_decode($raw);
        if ($imageData === false) return null;

        $image = @imagecreatefromstring($imageData);
        if ($image === false) return null;

        $filename = 'marketplace/' . \Illuminate\Support\Str::uuid() . '.webp';
        $tmpPath  = sys_get_temp_dir() . '/' . basename($filename);

        imagewebp($image, $tmpPath, 80);
        imagedestroy($image);

        $disk->put($filename, file_get_contents($tmpPath));
        @unlink($tmpPath);

        return $filename;
    }
    return null;
}
