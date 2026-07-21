import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

# Get running laravel pod
stdin, stdout, stderr = client.exec_command("kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}'")
pod = stdout.read().decode().strip().strip("'")
print(f"Laravel Pod: {pod}")

# Check storage/app/public/service
print("\n=== Listing storage/app/public/service ===")
stdin, stdout, stderr = client.exec_command(f"kubectl exec {pod} -- ls -lh storage/app/public/service 2>&1")
print(stdout.read().decode())

# Check public/storage/service
print("\n=== Listing public/storage/service ===")
stdin, stdout, stderr = client.exec_command(f"kubectl exec {pod} -- ls -lh public/storage/service 2>&1")
print(stdout.read().decode())

# Check if public/storage symlink is broken
print("\n=== Check public/storage symlink ===")
stdin, stdout, stderr = client.exec_command(f"kubectl exec {pod} -- ls -la public/storage 2>&1")
print(stdout.read().decode())

# Check database query for services and their images
stdin, stdout, stderr = client.exec_command(
    f"kubectl exec deployment/postgres -- psql -U picme_user -d picme_db -c \"SELECT id, name, image FROM services;\""
)
print("=== Services in Database ===")
print(stdout.read().decode())

client.close()
