import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

# Get running laravel pod
stdin, stdout, stderr = client.exec_command("kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}'")
pod = stdout.read().decode().strip().strip("'")

print(f"=== Pod: {pod} ===")

# Check .env
stdin, stdout, stderr = client.exec_command(f"kubectl exec {pod} -- grep -E 'FILESYSTEM_DISK|APP_URL|ASSET_URL|AWS_' .env")
print("=== Env Variables ===")
print(stdout.read().decode())

# Check storage symlink
stdin, stdout, stderr = client.exec_command(f"kubectl exec {pod} -- ls -la public/storage")
print("=== Storage Symlink ===")
print(stdout.read().decode())

client.close()
