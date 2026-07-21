import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

# Get running laravel pod
stdin, stdout, stderr = client.exec_command("kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}'")
pod = stdout.read().decode().strip().strip("'")

# List listings folder
stdin, stdout, stderr = client.exec_command(f"kubectl exec {pod} -- ls -lh storage/app/public/listings 2>&1")
print("=== Files inside listings folder inside pod ===")
print(stdout.read().decode())

# Check total size of public folder inside pod
stdin, stdout, stderr = client.exec_command(f"kubectl exec {pod} -- du -sh storage/app/public 2>&1")
print("=== Storage size inside pod ===")
print(stdout.read().decode())

client.close()
