import paramiko, tarfile

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

with tarfile.open('fixes_campaign.tar.gz', 'w:gz') as tar:
    tar.add('app/Http/Controllers/Resource/AdCampaignResource.php')
    tar.add('resources/views/admin/ad-campaign/show.blade.php')
    tar.add('resources/views/admin/ad-campaign/edit.blade.php')

sftp = client.open_sftp()
sftp.put('fixes_campaign.tar.gz', '/tmp/fixes_campaign.tar.gz')
sftp.close()

for label in ['app=laravel', 'app=laravel-worker']:
    stdin, stdout, stderr = client.exec_command(f"kubectl get pods -l {label} -o jsonpath='{{.items[*].metadata.name}}'")
    pods = stdout.read().decode().strip().strip("'").split()
    for pod in pods:
        if not pod: continue
        print(f"Deploying to {pod}...")
        client.exec_command(f"kubectl cp /tmp/fixes_campaign.tar.gz {pod}:/tmp/fixes_campaign.tar.gz")
        client.exec_command(f"kubectl exec {pod} -- tar -xzf /tmp/fixes_campaign.tar.gz -C /app")
        if 'worker' in label:
            i, o, e = client.exec_command(f"kubectl exec {pod} -- php artisan queue:restart")
            print("Worker restart:", o.read().decode())

print("Done deploying Ad Campaign fixes.")
client.close()
