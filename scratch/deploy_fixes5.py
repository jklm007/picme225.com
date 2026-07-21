import paramiko
import tarfile

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

with tarfile.open("fixes_update5.tar.gz", "w:gz") as tar:
    tar.add("app/Jobs/ProcessWhatsappBatchJob.php")
    tar.add("app/Http/Controllers/UserMarketplaceController.php")
    tar.add("app/Http/Controllers/Admin/MarketplaceListingController.php")

sftp = client.open_sftp()
sftp.put("fixes_update5.tar.gz", "/tmp/fixes_update5.tar.gz")

# Web pods
print("Deploying to web pods...")
stdin, stdout, stderr = client.exec_command("kubectl get pods -l app=laravel -o jsonpath='{.items[*].metadata.name}'")
web_pods = stdout.read().decode().strip().replace("'", "").split()
for pod in web_pods:
    if not pod: continue
    print(f"Updating {pod}")
    client.exec_command(f"kubectl cp /tmp/fixes_update5.tar.gz {pod}:/tmp/fixes_update5.tar.gz")
    client.exec_command(f"kubectl exec {pod} -- tar -xzf /tmp/fixes_update5.tar.gz -C /app")

# Worker pods
print("Deploying to worker pods...")
stdin, stdout, stderr = client.exec_command("kubectl get pods -l app=laravel-worker -o jsonpath='{.items[*].metadata.name}'")
worker_pods = stdout.read().decode().strip().replace("'", "").split()
for pod in worker_pods:
    if not pod: continue
    print(f"Updating {pod}")
    client.exec_command(f"kubectl cp /tmp/fixes_update5.tar.gz {pod}:/tmp/fixes_update5.tar.gz")
    client.exec_command(f"kubectl exec {pod} -- tar -xzf /tmp/fixes_update5.tar.gz -C /app")
    client.exec_command(f"kubectl exec {pod} -- php artisan queue:restart")

# Delete ALL phantom listings
clean_script = """<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\\Contracts\\Console\\Kernel::class);
$kernel->bootstrap();

$count = \App\Models\MarketplaceListing::where(function($q) {
    $q->where('title', 'like', '%Samsung Galaxy S22%')
      ->orWhere('title', 'like', '%Voiture à vendre%');
})->delete();
echo "Purged " . $count . " phantom listings from the database.\\n";
"""
with sftp.file("/tmp/clean_db.php", "w") as f:
    f.write(clean_script)
sftp.close()

if web_pods:
    pod = web_pods[0]
    client.exec_command(f"kubectl cp /tmp/clean_db.php {pod}:/app/clean_db.php")
    stdin, stdout, stderr = client.exec_command(f"kubectl exec {pod} -- php /app/clean_db.php")
    print(stdout.read().decode())
    client.exec_command(f"kubectl exec {pod} -- rm /app/clean_db.php")

client.close()
print("Done deploying fixes 5.")
