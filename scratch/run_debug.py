import paramiko
client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

sftp = client.open_sftp()
sftp.put("scratch/debug_media.php", "/tmp/debug_media.php")
sftp.close()

stdin, stdout, stderr = client.exec_command("kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}'")
pod = stdout.read().decode().strip().strip("'")

client.exec_command(f"kubectl cp /tmp/debug_media.php {pod}:/app/debug_media.php")
stdin, stdout, stderr = client.exec_command(f"kubectl exec {pod} -- php /app/debug_media.php")
print("Debug Output:\n", stdout.read().decode())
print("Debug Error:\n", stderr.read().decode())
client.exec_command(f"kubectl exec {pod} -- rm /app/debug_media.php")

client.close()
