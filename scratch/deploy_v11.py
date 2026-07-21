import paramiko, tarfile, time, os

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

print("Building PWA package archive...")
with tarfile.open('fixes_v11.tar.gz', 'w:gz') as tar:
    # Adding manifest and service worker
    tar.add('public/manifest.json')
    tar.add('public/serviceworker.js')
    
    # Adding blade views
    tar.add('resources/views/offline.blade.php')
    tar.add('resources/views/common/pwa_installer.blade.php')
    tar.add('resources/views/user/layout/app.blade.php')
    tar.add('resources/views/user/layout/pwa.blade.php')
    tar.add('resources/views/provider/layout/app.blade.php')
    tar.add('resources/views/user/layout/base.blade.php')

sftp = client.open_sftp()
sftp.put('fixes_v11.tar.gz', '/tmp/fixes_v11.tar.gz')
sftp.close()
print("Archive uploaded to remote host.")

for label in ['app=laravel', 'app=laravel-worker']:
    stdin, stdout, stderr = client.exec_command("kubectl get pods -l " + label + " -o jsonpath='{.items[*].metadata.name}'")
    pods = stdout.read().decode().strip().strip("'").split()
    for pod in pods:
        if not pod: continue
        print("Deploying to pod: " + pod)
        client.exec_command("kubectl cp /tmp/fixes_v11.tar.gz " + pod + ":/tmp/fixes_v11.tar.gz")
        time.sleep(1.5)
        client.exec_command("kubectl exec " + pod + " -- tar -xzf /tmp/fixes_v11.tar.gz -C /app")
        time.sleep(1.5)
        if 'worker' in label:
            i, o, e = client.exec_command("kubectl exec " + pod + " -- php artisan queue:restart")
            print("  Worker restart: " + o.read().decode().strip())

pod = "laravel-deployment-56f54497f-r8pmg"

print("Clearing Laravel view and config cache...")
client.exec_command("kubectl exec " + pod + " -- php artisan view:clear")
time.sleep(1)
client.exec_command("kubectl exec " + pod + " -- php artisan config:clear")
time.sleep(1)
stdin, stdout, stderr = client.exec_command("kubectl exec " + pod + " -- php artisan cache:clear 2>&1")
print(stdout.read().decode().strip())

client.close()
print("DONE DEPLOYING PWA CHANGES!")
