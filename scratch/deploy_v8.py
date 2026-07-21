import paramiko, tarfile

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

with tarfile.open('fixes_v8_selfheal.tar.gz', 'w:gz') as tar:
    tar.add('app/Jobs/ProcessWhatsappBatchJob.php')

sftp = client.open_sftp()
sftp.put('fixes_v8_selfheal.tar.gz', '/tmp/fixes_v8_selfheal.tar.gz')
sftp.close()

for label in ['app=laravel', 'app=laravel-worker']:
    stdin, stdout, stderr = client.exec_command(f"kubectl get pods -l {label} -o jsonpath='{{.items[*].metadata.name}}'")
    pods = stdout.read().decode().strip().strip("'").split()
    for pod in pods:
        if not pod: continue
        print(f"Deploying to {pod}...")
        client.exec_command(f"kubectl cp /tmp/fixes_v8_selfheal.tar.gz {pod}:/tmp/fixes_v8_selfheal.tar.gz")
        client.exec_command(f"kubectl exec {pod} -- tar -xzf /tmp/fixes_v8_selfheal.tar.gz -C /app")
        if 'worker' in label:
            i, o, e = client.exec_command(f"kubectl exec {pod} -- php artisan queue:restart")
            print("Worker restart:", o.read().decode())

print("Done deploying self-healing AI engine v8.")
client.close()
