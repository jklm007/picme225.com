import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

# Get all running laravel pods
stdin, stdout, stderr = client.exec_command("kubectl get pods -l app=laravel -o jsonpath='{.items[*].metadata.name}'")
pods = stdout.read().decode().strip().split()
print(f"Laravel Pods found: {pods}")

for pod in pods:
    print(f"\n==========================================")
    print(f"Fixing storage for pod: {pod}")
    print(f"==========================================")
    
    # 1. Print current public/storage details
    stdin, stdout, stderr = client.exec_command(f"kubectl exec {pod} -- ls -la public/storage 2>&1")
    print("Before fix (public/storage):")
    print(stdout.read().decode())
    
    # 2. Remove real folder if it exists, and recreate the symlink
    cmd_fix = (
        "cd /app && "
        "rm -rf public/storage && "
        "php artisan storage:link && "
        "chmod -R 775 storage && "
        "chown -R application:application storage"
    )
    stdin, stdout, stderr = client.exec_command(f"kubectl exec {pod} -- bash -c '{cmd_fix}' 2>&1")
    print("Run storage:link output:")
    print(stdout.read().decode())
    
    # 3. Check public/storage details again
    stdin, stdout, stderr = client.exec_command(f"kubectl exec {pod} -- ls -la public/storage 2>&1")
    print("After fix (public/storage):")
    print(stdout.read().decode())

    # 4. Check if marketplace, listings, and service directories exist now inside storage/app/public/
    stdin, stdout, stderr = client.exec_command(f"kubectl exec {pod} -- ls -la storage/app/public 2>&1")
    print("Contents of storage/app/public:")
    print(stdout.read().decode())

client.close()
