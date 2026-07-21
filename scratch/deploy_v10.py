import paramiko, tarfile, time, os

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

print("Building archive...")
with tarfile.open('fixes_v10.tar.gz', 'w:gz') as tar:
    tar.add('app/Jobs/ProcessWhatsappBatchJob.php')
    tar.add('app/Http/Controllers/Resource/AdCampaignResource.php')
    tar.add('resources/views/admin/ad-campaign/edit.blade.php')
    tar.add('resources/views/admin/ad-campaign/show.blade.php')

sftp = client.open_sftp()
sftp.put('fixes_v10.tar.gz', '/tmp/fixes_v10.tar.gz')
sftp.close()
print("Archive uploaded.")

for label in ['app=laravel', 'app=laravel-worker']:
    stdin, stdout, stderr = client.exec_command("kubectl get pods -l " + label + " -o jsonpath='{.items[*].metadata.name}'")
    pods = stdout.read().decode().strip().strip("'").split()
    for pod in pods:
        if not pod: continue
        print("Deploying to: " + pod)
        client.exec_command("kubectl cp /tmp/fixes_v10.tar.gz " + pod + ":/tmp/fixes_v10.tar.gz")
        time.sleep(1)
        client.exec_command("kubectl exec " + pod + " -- tar -xzf /tmp/fixes_v10.tar.gz -C /app")
        time.sleep(1)
        if 'worker' in label:
            i, o, e = client.exec_command("kubectl exec " + pod + " -- php artisan queue:restart")
            print("Worker restart: " + o.read().decode().strip())

pod = "laravel-deployment-56f54497f-r8pmg"

print("Clearing Groq cache...")
clear_php_content = """<?php
require '/app/vendor/autoload.php';
$app = require '/app/bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();
use Illuminate\\Support\\Facades\\Cache;
Cache::forget('groq_available_vision_models');
Cache::forget('groq_unavailable_models');
Cache::forget('groq_last_working_model');
echo "Groq cache cleared!\\n";
"""
with open('scratch/clear_groq_cache_v10.php', 'w') as f:
    f.write(clear_php_content)

sftp = client.open_sftp()
sftp.put('scratch/clear_groq_cache_v10.php', '/tmp/clear_groq_cache.php')
sftp.close()
client.exec_command("kubectl cp /tmp/clear_groq_cache.php " + pod + ":/tmp/clear_groq_cache.php")
time.sleep(1)
stdin, stdout, stderr = client.exec_command("kubectl exec " + pod + " -- php /tmp/clear_groq_cache.php 2>&1")
print(stdout.read().decode().strip())

print("Full cache clear...")
stdin, stdout, stderr = client.exec_command("kubectl exec " + pod + " -- php artisan cache:clear 2>&1")
print(stdout.read().decode().strip())

client.close()
print("DONE!")
