import paramiko
client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

sftp = client.open_sftp()
sftp.put("scratch/cleanup_ghosts.php", "/tmp/cleanup_ghosts.php")
sftp.close()

stdin, stdout, stderr = client.exec_command("kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}'")
pod = stdout.read().decode().strip().strip("'")

client.exec_command(f"kubectl cp /tmp/cleanup_ghosts.php {pod}:/app/cleanup_ghosts.php")
stdin, stdout, stderr = client.exec_command(f"kubectl exec {pod} -- php /app/cleanup_ghosts.php")
print("Cleanup:", stdout.read().decode(), stderr.read().decode())
client.exec_command(f"kubectl exec {pod} -- rm /app/cleanup_ghosts.php")

client.close()
