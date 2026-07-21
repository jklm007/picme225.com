import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

debug_php = """<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\\Contracts\\Console\\Kernel::class);
$kernel->bootstrap();

$listings = \App\Models\MarketplaceListing::whereNotNull('cover_image')->orderBy('id', 'desc')->take(10)->get();
foreach($listings as $listing) {
    echo "ID: " . $listing->id . " | RAW: " . $listing->getRawOriginal('cover_image') . " | URL: " . $listing->media_url . "\\n";
}
"""

sftp = client.open_sftp()
with sftp.file("/tmp/debug_list.php", "w") as f:
    f.write(debug_php)
sftp.close()

stdin, stdout, stderr = client.exec_command("kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}'")
pod = stdout.read().decode().strip().strip("'")

client.exec_command(f"kubectl cp /tmp/debug_list.php {pod}:/app/debug_list.php")
stdin, stdout, stderr = client.exec_command(f"kubectl exec {pod} -- php /app/debug_list.php")
print(stdout.read().decode() + stderr.read().decode())
client.exec_command(f"kubectl exec {pod} -- rm /app/debug_list.php")
client.close()
