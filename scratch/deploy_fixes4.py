import paramiko
import tarfile

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

with tarfile.open("fixes_update4.tar.gz", "w:gz") as tar:
    tar.add("app/Jobs/ProcessWhatsappBatchJob.php")

sftp = client.open_sftp()
sftp.put("fixes_update4.tar.gz", "/tmp/fixes_update4.tar.gz")

print("Deploying to worker pods...")
stdin, stdout, stderr = client.exec_command("kubectl get pods -l app=laravel-worker -o jsonpath='{.items[*].metadata.name}'")
worker_pods = stdout.read().decode().strip().replace("'", "").split()
for pod in worker_pods:
    if not pod: continue
    print(f"Updating {pod}")
    client.exec_command(f"kubectl cp /tmp/fixes_update4.tar.gz {pod}:/tmp/fixes_update4.tar.gz")
    client.exec_command(f"kubectl exec {pod} -- tar -xzf /tmp/fixes_update4.tar.gz -C /app")
    client.exec_command(f"kubectl exec {pod} -- php artisan queue:restart")

# Delete the phantom 'Voiture à vendre' listing
clean_script = """<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\\Contracts\\Console\\Kernel::class);
$kernel->bootstrap();

$deleted = \App\Models\MarketplaceListing::where('title', 'Voiture à vendre')->delete();
echo "Deleted " . $deleted . " phantom listings.\\n";
"""
with sftp.file("/tmp/clean_db.php", "w") as f:
    f.write(clean_script)
sftp.close()

if worker_pods:
    pod = worker_pods[0]
    client.exec_command(f"kubectl cp /tmp/clean_db.php {pod}:/app/clean_db.php")
    stdin, stdout, stderr = client.exec_command(f"kubectl exec {pod} -- php /app/clean_db.php")
    print(stdout.read().decode())
    client.exec_command(f"kubectl exec {pod} -- rm /app/clean_db.php")

client.close()
print("Done deploying fixes 4.")
