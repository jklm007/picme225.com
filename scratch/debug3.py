import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

debug_php = """<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\\Contracts\\Console\\Kernel::class);
$kernel->bootstrap();

$listing = \App\Models\MarketplaceListing::orderBy('id', 'desc')->first();
echo "Listing ID: " . $listing->id . "\\n";
echo "Title: " . $listing->title . "\\n";
echo "Cover Image RAW: " . $listing->getRawOriginal('cover_image') . "\\n";
echo "Media URL: " . $listing->media_url . "\\n";

$images = $listing->getRawOriginal('images');
echo "Images JSON: " . $images . "\\n";

// Check if file actually exists on S3 disk
$disk = env('FILESYSTEM_DISK', 'public');
$path = $listing->getRawOriginal('cover_image');

if (str_starts_with($path, 'http')) {
    if (str_contains($path, '/storage/')) {
        $path = substr($path, strpos($path, '/storage/') + 9);
    }
}
if (str_starts_with($path, 'storage/')) {
    $path = substr($path, 8);
}

try {
    $exists = \Illuminate\Support\Facades\Storage::disk($disk)->exists($path);
    echo "Exists on disk '" . $disk . "': " . ($exists ? 'YES' : 'NO') . "\\n";
} catch (\Exception $e) {
    echo "Error checking disk: " . $e->getMessage() . "\\n";
}
"""

sftp = client.open_sftp()
with sftp.file("/tmp/debug3.php", "w") as f:
    f.write(debug_php)
sftp.close()

stdin, stdout, stderr = client.exec_command("kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}'")
pod = stdout.read().decode().strip().strip("'")

client.exec_command(f"kubectl cp /tmp/debug3.php {pod}:/app/debug3.php")
stdin, stdout, stderr = client.exec_command(f"kubectl exec {pod} -- php /app/debug3.php")
print("Output:\n" + stdout.read().decode() + stderr.read().decode())
client.exec_command(f"kubectl exec {pod} -- rm /app/debug3.php")
client.close()
