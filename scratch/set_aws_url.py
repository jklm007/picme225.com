import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

print("Setting AWS_URL on laravel-deployment...")
stdin, stdout, stderr = client.exec_command("kubectl set env deployment/laravel-deployment AWS_URL=https://media.picme225.site")
print(stdout.read().decode())
print(stderr.read().decode())

print("Setting AWS_URL on jklm-web-deployment (if applicable)...")
client.exec_command("kubectl set env deployment/jklm-web-deployment AWS_URL=https://media.picme225.site")

print("Setting AWS_URL on epdd-deployment (if applicable)...")
client.exec_command("kubectl set env deployment/epdd-deployment AWS_URL=https://media.picme225.site")

client.close()
print("Done. Kubernetes is rolling out the update.")
