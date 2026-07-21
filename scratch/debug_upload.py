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
echo "Disk: " . $disk . "\\n";
try {
    $content = 'test content';
    $path = \Illuminate\Support\Facades\Storage::disk($disk)->put('marketplace/test.txt', $content);
    echo "Put returned: " . ($path ? "true" : "false") . "\\n";
    echo "Exists? " . (\Illuminate\Support\Facades\Storage::disk($disk)->exists('marketplace/test.txt') ? "YES" : "NO") . "\\n";
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\\n";
}
"""

sftp = client.open_sftp()
with sftp.file("/tmp/debug_upload.php", "w") as f:
    f.write(debug_php)
sftp.close()

stdin, stdout, stderr = client.exec_command("kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}'")
pod = stdout.read().decode().strip().strip("'")

client.exec_command(f"kubectl cp /tmp/debug_upload.php {pod}:/app/debug_upload.php")
stdin, stdout, stderr = client.exec_command(f"kubectl exec {pod} -- php /app/debug_upload.php")
print(stdout.read().decode() + stderr.read().decode())
client.exec_command(f"kubectl exec {pod} -- rm /app/debug_upload.php")
client.close()
