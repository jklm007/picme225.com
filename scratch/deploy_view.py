import paramiko
import os

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

# Upload the file via SFTP
sftp = client.open_sftp()
local_file = 'resources/views/admin/marketplace/listings/_listing_row.blade.php'
remote_file = '/tmp/_listing_row.blade.php'
sftp.put(local_file, remote_file)
sftp.close()

# Copy into pod
stdin, stdout, stderr = client.exec_command("kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}'")
pod = stdout.read().decode().strip().strip("'")

cmd = f"kubectl cp {remote_file} {pod}:/app/resources/views/admin/marketplace/listings/_listing_row.blade.php"
stdin, stdout, stderr = client.exec_command(cmd)
print("CP:", stdout.read().decode(), stderr.read().decode())

cmd = f"kubectl exec {pod} -- php artisan view:clear"
stdin, stdout, stderr = client.exec_command(cmd)
print("CLEAR:", stdout.read().decode(), stderr.read().decode())

client.close()
