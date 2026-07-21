import paramiko

hostname = '109.199.123.69'
username = 'root'
password = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(hostname, username=username, password=password)

# Fix permissions in laravel-deployment pod
print("Fixing permissions in laravel-deployment...")
cmd1 = """kubectl exec deploy/laravel-deployment -- chown application:application storage/oauth-private.key storage/oauth-public.key && kubectl exec deploy/laravel-deployment -- chmod 600 storage/oauth-private.key && kubectl exec deploy/laravel-deployment -- chmod 644 storage/oauth-public.key"""
stdin1, stdout1, stderr1 = client.exec_command(cmd1)
print("Web Result:", stdout1.read().decode('utf-8', errors='replace'))
print("Web Error:", stderr1.read().decode('utf-8', errors='replace'))

# Fix permissions in laravel-worker pod
print("Fixing permissions in laravel-worker...")
cmd2 = """kubectl exec deploy/laravel-worker -- chown application:application storage/oauth-private.key storage/oauth-public.key && kubectl exec deploy/laravel-worker -- chmod 600 storage/oauth-private.key && kubectl exec deploy/laravel-worker -- chmod 644 storage/oauth-public.key"""
stdin2, stdout2, stderr2 = client.exec_command(cmd2)
print("Worker Result:", stdout2.read().decode('utf-8', errors='replace'))
print("Worker Error:", stderr2.read().decode('utf-8', errors='replace'))

client.close()
