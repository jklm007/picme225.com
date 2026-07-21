import paramiko
import tarfile

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

with tarfile.open("fixes_update6.tar.gz", "w:gz") as tar:
    tar.add("app/Http/Controllers/Admin/MarketplaceListingController.php")

sftp = client.open_sftp()
sftp.put("fixes_update6.tar.gz", "/tmp/fixes_update6.tar.gz")

# Web pods
print("Deploying to web pods...")
stdin, stdout, stderr = client.exec_command("kubectl get pods -l app=laravel -o jsonpath='{.items[*].metadata.name}'")
web_pods = stdout.read().decode().strip().replace("'", "").split()
for pod in web_pods:
    if not pod: continue
    print(f"Updating {pod}")
    client.exec_command(f"kubectl cp /tmp/fixes_update6.tar.gz {pod}:/tmp/fixes_update6.tar.gz")
    client.exec_command(f"kubectl exec {pod} -- tar -xzf /tmp/fixes_update6.tar.gz -C /app")

print("Done deploying fixes 6.")
