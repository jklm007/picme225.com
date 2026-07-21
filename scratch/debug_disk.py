import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

debug_php = """<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\\Contracts\\Console\\Kernel::class);
$kernel->bootstrap();

$disk = env('FILESYSTEM_DISK', 'public');
echo "Checking Disk: " . $disk . "\\n";

$exists = \Illuminate\Support\Facades\Storage::disk($disk)->exists('marketplace/NzwY8mYijMh84Y3y1FLQcnxo2heXZdvxeCevCvvw.jpg');
echo "marketplace/...jpg exists? " . ($exists ? "YES" : "NO") . "\\n";

try {
    $files = \Illuminate\Support\Facades\Storage::disk($disk)->files('marketplace');
    echo "Files in marketplace/: " . count($files) . "\\n";
    $files2 = \Illuminate\Support\Facades\Storage::disk($disk)->files('listings');
    echo "Files in listings/: " . count($files2) . "\\n";
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\\n";
}
"""

sftp = client.open_sftp()
with sftp.file("/tmp/debug_disk.php", "w") as f:
    f.write(debug_php)
sftp.close()

stdin, stdout, stderr = client.exec_command("kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}'")
pod = stdout.read().decode().strip().strip("'")

client.exec_command(f"kubectl cp /tmp/debug_disk.php {pod}:/app/debug_disk.php")
stdin, stdout, stderr = client.exec_command(f"kubectl exec {pod} -- php /app/debug_disk.php")
print(stdout.read().decode() + stderr.read().decode())
client.exec_command(f"kubectl exec {pod} -- rm /app/debug_disk.php")
client.close()
