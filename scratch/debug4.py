import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

debug_php = """<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\\Contracts\\Console\\Kernel::class);
$kernel->bootstrap();

$listings = \App\Models\MarketplaceListing::orderBy('id', 'desc')->take(5)->get();
foreach($listings as $l) {
    echo "ID: " . $l->id . " | Title: " . $l->title . " | Date: " . $l->created_at . " | Media URL: " . $l->media_url . "\\n";
}
"""

sftp = client.open_sftp()
with sftp.file("/tmp/debug4.php", "w") as f:
    f.write(debug_php)
sftp.close()

stdin, stdout, stderr = client.exec_command("kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}'")
pod = stdout.read().decode().strip().strip("'")

client.exec_command(f"kubectl cp /tmp/debug4.php {pod}:/app/debug4.php")
stdin, stdout, stderr = client.exec_command(f"kubectl exec {pod} -- php /app/debug4.php")
print(stdout.read().decode() + stderr.read().decode())
client.exec_command(f"kubectl exec {pod} -- rm /app/debug4.php")
client.close()
