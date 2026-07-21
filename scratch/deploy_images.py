import paramiko
import os

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

# Upload the tar file via SFTP
sftp = client.open_sftp()
local_file = 'service.tar.gz'
remote_file = '/tmp/service.tar.gz'
sftp.put(local_file, remote_file)
sftp.close()

# Copy into pod and extract
stdin, stdout, stderr = client.exec_command("kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}'")
pod = stdout.read().decode().strip().strip("'")

cmd = f"kubectl cp {remote_file} {pod}:/tmp/service.tar.gz"
stdin, stdout, stderr = client.exec_command(cmd)

cmd2 = f"kubectl exec {pod} -- tar -xzf /tmp/service.tar.gz -C /app/storage/app/public"
stdin, stdout, stderr = client.exec_command(cmd2)

client.close()
print("Deployed images successfully.")
