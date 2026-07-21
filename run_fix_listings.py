import paramiko
import base64

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

stdin, stdout, stderr = client.exec_command("kubectl get pod -l app=laravel -o jsonpath='{.items[0].metadata.name}'")
pod = stdout.read().decode().strip()

php_script = """<?php
require '/app/vendor/autoload.php';
$app = require_once '/app/bootstrap/app.php';
$kernel = $app->make(Illuminate\\Contracts\\Console\\Kernel::class);
$kernel->bootstrap();

$disk = \\Illuminate\\Support\\Facades\\Storage::disk('s3');
echo "Fetching R2 file list...\\n";
$r2Files = $disk->allFiles();
echo "Total R2 files: " . count($r2Files) . "\\n";

// Map prefix (first 8 chars of filename) to actual R2 path
$r2Map = [];
foreach ($r2Files as $file) {
    $filename = basename($file);
    $prefix = substr($filename, 0, 8);
    if (strlen($prefix) === 8) {
        $r2Map[$prefix] = $file;
    }
}

$listings = \\App\\Models\\MarketplaceListing::all();
foreach ($listings as $listing) {
    $images = $listing->images;
    if (empty($images) || !is_array($images)) continue;
    
    $updatedImages = [];
    $changed = false;
    
    foreach ($images as $img) {
        // Extract filename if it is a URL
        $filename = basename($img);
        $prefix = substr($filename, 0, 8);
        
        if (isset($r2Map[$prefix])) {
            $correctPath = $r2Map[$prefix];
            if ($img !== $correctPath) {
                echo "Listing #{$listing->id}: replacing '{$img}' -> '{$correctPath}'\\n";
                $updatedImages[] = $correctPath;
                $changed = true;
            } else {
                $updatedImages[] = $img;
            }
        } else {
            echo "Listing #{$listing->id}: prefix '{$prefix}' (from '{$img}') NOT found on R2! Keeping original.\\n";
            $updatedImages[] = $img;
        }
    }
    
    // Check cover image
    $cover = $listing->cover_image;
    $newCover = $cover;
    if ($cover) {
        $filename = basename($cover);
        $prefix = substr($filename, 0, 8);
        if (isset($r2Map[$prefix])) {
            $newCover = $r2Map[$prefix];
            if ($cover !== $newCover) {
                echo "Listing #{$listing->id}: updating cover '{$cover}' -> '{$newCover}'\\n";
                $changed = true;
            }
        }
    }
    
    if ($changed) {
        $listing->images = $updatedImages;
        $listing->cover_image = $newCover;
        $listing->save();
        echo "Listing #{$listing->id} successfully updated!\\n";
    }
}
"""

encoded = base64.b64encode(php_script.encode()).decode()
command = f"kubectl exec {pod} -- bash -c \"echo '{encoded}' | base64 -d > /app/public/fix_listings.php && php /app/public/fix_listings.php\""
stdin, stdout, stderr = client.exec_command(command)
print(stdout.read().decode())
print(stderr.read().decode())
