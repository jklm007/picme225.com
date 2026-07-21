import paramiko
import tarfile

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

# Package the model
with tarfile.open("model_update.tar.gz", "w:gz") as tar:
    tar.add("app/Models/MarketplaceListing.php")

sftp = client.open_sftp()
sftp.put("model_update.tar.gz", "/tmp/model_update.tar.gz")
sftp.close()

stdin, stdout, stderr = client.exec_command("kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}'")
pod = stdout.read().decode().strip().strip("'")

client.exec_command(f"kubectl cp /tmp/model_update.tar.gz {pod}:/tmp/model_update.tar.gz")
stdin, stdout, stderr = client.exec_command(f"kubectl exec {pod} -- tar -xzf /tmp/model_update.tar.gz -C /app")
print("Extracted model:", stdout.read().decode(), stderr.read().decode())

# Also clear view cache
stdin, stdout, stderr = client.exec_command(f"kubectl exec {pod} -- php artisan view:clear")
print("View cache cleared:", stdout.read().decode())

client.close()
print("Done.")
