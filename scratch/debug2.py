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
"""

sftp = client.open_sftp()
with sftp.file("/tmp/debug2.php", "w") as f:
    f.write(debug_php)
sftp.close()

stdin, stdout, stderr = client.exec_command("kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}'")
pod = stdout.read().decode().strip().strip("'")

client.exec_command(f"kubectl cp /tmp/debug2.php {pod}:/app/debug2.php")
stdin, stdout, stderr = client.exec_command(f"kubectl exec {pod} -- php /app/debug2.php")
print("Output:\n" + stdout.read().decode() + stderr.read().decode())
client.exec_command(f"kubectl exec {pod} -- rm /app/debug2.php")
client.close()
