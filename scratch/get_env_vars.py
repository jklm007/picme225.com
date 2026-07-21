import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

# Get running laravel pod
stdin, stdout, stderr = client.exec_command("kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}'")
pod = stdout.read().decode().strip().strip("'")
print(f"Laravel Pod: {pod}")

# Print env
stdin, stdout, stderr = client.exec_command(f"kubectl exec {pod} -- env")
print("=== Pod Environment ===")
print(stdout.read().decode())

client.close()
