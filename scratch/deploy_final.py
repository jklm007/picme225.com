import paramiko, tarfile, time

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

print("=== Deploying all fixes ===")
with tarfile.open('fixes_final2.tar.gz', 'w:gz') as tar:
    tar.add('app/Jobs/ProcessWhatsappBatchJob.php')
    tar.add('app/Http/Controllers/Resource/AdCampaignResource.php')
    tar.add('resources/views/admin/ad-campaign/edit.blade.php')
    tar.add('resources/views/admin/ad-campaign/show.blade.php')

sftp = client.open_sftp()
sftp.put('fixes_final2.tar.gz', '/tmp/fixes_final2.tar.gz')
sftp.close()

for label in ['app=laravel', 'app=laravel-worker']:
    stdin, stdout, stderr = client.exec_command(f"kubectl get pods -l {label} -o jsonpath='{{.items[*].metadata.name}}'")
    pods = stdout.read().decode().strip().strip("'").split()
    for pod in pods:
        if not pod: continue
        print(f"  → Deploying to {pod}...")
        _, _, _ = client.exec_command(f"kubectl cp /tmp/fixes_final2.tar.gz {pod}:/tmp/fixes_final2.tar.gz")
        time.sleep(1)
        _, _, _ = client.exec_command(f"kubectl exec {pod} -- tar -xzf /tmp/fixes_final2.tar.gz -C /app")
        time.sleep(1)
        if 'worker' in label:
            i, o, e = client.exec_command(f"kubectl exec {pod} -- php artisan queue:restart")
            print("    Worker restart:", o.read().decode().strip())

# Clear all caches
pod = "laravel-deployment-56f54497f-r8pmg"
print("\n=== Clearing caches ===")
_, o, _ = client.exec_command(f"kubectl exec {pod} -- php artisan cache:clear")
print(o.read().decode().strip())

# Verify the fix is deployed on pod
print("\n=== Verifying fix on pod ===")
_, o, _ = client.exec_command(f"kubectl exec {pod} -- grep -c 'think' /app/app/Jobs/ProcessWhatsappBatchJob.php")
print(f"  <think> occurrences in code: {o.read().decode().strip()}")

_, o, _ = client.exec_command(f"kubectl exec {pod} -- grep -c 'mixtral' /app/app/Jobs/ProcessWhatsappBatchJob.php")
count = o.read().decode().strip()
print(f"  mixtral occurrences in code (should be 0): {count}")

print("\n=== All done! ===")
client.close()
