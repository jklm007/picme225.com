import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

pod = "laravel-deployment-56f54497f-r8pmg"
cmd = f"kubectl exec {pod} -- php artisan cache:clear"
stdin, stdout, stderr = client.exec_command(cmd)
print("Cache clear result:")
print(stdout.read().decode())
client.close()
