import paramiko
import tarfile

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

with tarfile.open("fixes_update7.tar.gz", "w:gz") as tar:
    tar.add("app/Jobs/ProcessWhatsappBatchJob.php")

sftp = client.open_sftp()
sftp.put("fixes_update7.tar.gz", "/tmp/fixes_update7.tar.gz")
sftp.close()

# Web pods
stdin, stdout, stderr = client.exec_command("kubectl get pods -l app=laravel -o jsonpath='{.items[*].metadata.name}'")
web_pods = stdout.read().decode().strip().replace("'", "").split()
for pod in web_pods:
    if not pod: continue
    client.exec_command(f"kubectl cp /tmp/fixes_update7.tar.gz {pod}:/tmp/fixes_update7.tar.gz")
    client.exec_command(f"kubectl exec {pod} -- tar -xzf /tmp/fixes_update7.tar.gz -C /app")

# Worker pods
stdin, stdout, stderr = client.exec_command("kubectl get pods -l app=laravel-worker -o jsonpath='{.items[*].metadata.name}'")
worker_pods = stdout.read().decode().strip().replace("'", "").split()
for pod in worker_pods:
    if not pod: continue
    print(f"Deploying to worker {pod}")
    client.exec_command(f"kubectl cp /tmp/fixes_update7.tar.gz {pod}:/tmp/fixes_update7.tar.gz")
    client.exec_command(f"kubectl exec {pod} -- tar -xzf /tmp/fixes_update7.tar.gz -C /app")
    stdin, stdout, stderr = client.exec_command(f"kubectl exec {pod} -- php artisan queue:restart")
    print("Queue restart:", stdout.read().decode() + stderr.read().decode())

print("Done. Model updated to meta-llama/llama-4-scout-17b-16e-instruct")

# Check notifications log too
print("\n=== Recent Notification Logs ===")
for pod in worker_pods[:1]:
    stdin, stdout, stderr = client.exec_command(f"kubectl logs --tail=200 {pod} | grep -E 'notifyUser|notifyAdmin|Evolution|message envoye|echec envoi|PENDING_VALIDATION|ACTIVE'")
    print(stdout.read().decode())

client.close()
