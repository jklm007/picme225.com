import paramiko
import tarfile

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

with tarfile.open("fixes_update2.tar.gz", "w:gz") as tar:
    tar.add("app/Models/MarketplaceListing.php")
    tar.add("resources/views/admin/marketplace/listings/edit.blade.php")
    tar.add("resources/views/admin/marketplace/listings/_listing_row.blade.php")

sftp = client.open_sftp()
sftp.put("fixes_update2.tar.gz", "/tmp/fixes_update2.tar.gz")
sftp.close()

stdin, stdout, stderr = client.exec_command("kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}'")
pod = stdout.read().decode().strip().strip("'")

client.exec_command(f"kubectl cp /tmp/fixes_update2.tar.gz {pod}:/tmp/fixes_update2.tar.gz")
stdin, stdout, stderr = client.exec_command(f"kubectl exec {pod} -- tar -xzf /tmp/fixes_update2.tar.gz -C /app")
print("Extracted:", stdout.read().decode(), stderr.read().decode())

# Clear view cache
stdin, stdout, stderr = client.exec_command(f"kubectl exec {pod} -- php artisan view:clear")
print("View cache cleared:", stdout.read().decode())

client.close()
print("Done deploying fixes 2.")
