import paramiko
import tarfile

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

# Package both files
with tarfile.open("fixes_update.tar.gz", "w:gz") as tar:
    tar.add("app/Models/MarketplaceListing.php")
    tar.add("app/Jobs/ProcessWhatsappBatchJob.php")

sftp = client.open_sftp()
sftp.put("fixes_update.tar.gz", "/tmp/fixes_update.tar.gz")
sftp.close()

stdin, stdout, stderr = client.exec_command("kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}'")
pod = stdout.read().decode().strip().strip("'")

client.exec_command(f"kubectl cp /tmp/fixes_update.tar.gz {pod}:/tmp/fixes_update.tar.gz")
stdin, stdout, stderr = client.exec_command(f"kubectl exec {pod} -- tar -xzf /tmp/fixes_update.tar.gz -C /app")
print("Extracted models & jobs:", stdout.read().decode(), stderr.read().decode())

# Clear view cache (for the model issue)
stdin, stdout, stderr = client.exec_command(f"kubectl exec {pod} -- php artisan view:clear")
print("View cache cleared:", stdout.read().decode())

# Restart queue workers (crucial for the Job changes to take effect)
stdin, stdout, stderr = client.exec_command(f"kubectl exec {pod} -- php artisan queue:restart")
print("Queue workers restarted:", stdout.read().decode())

client.close()
print("Done deploying fixes.")
