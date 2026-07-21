import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

print("Fetching pods...")
stdin, stdout, stderr = client.exec_command("kubectl get pods")
print(stdout.read().decode())

print("Fetching deployments...")
stdin, stdout, stderr = client.exec_command("kubectl get deployments")
print(stdout.read().decode())

client.close()
