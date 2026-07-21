import paramiko

hostname = '109.199.123.69'
username = 'root'
password = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(hostname, username=username, password=password)

# Generate passport keys in web deployment
print("Generating passport keys in laravel-deployment...")
cmd1 = """kubectl exec deploy/laravel-deployment -- php artisan passport:keys --force"""
stdin1, stdout1, stderr1 = client.exec_command(cmd1)
print("Web Result:", stdout1.read().decode('utf-8', errors='replace'))
print("Web Error:", stderr1.read().decode('utf-8', errors='replace'))

# Generate passport keys in worker deployment
print("Generating passport keys in laravel-worker...")
cmd2 = """kubectl exec deploy/laravel-worker -- php artisan passport:keys --force"""
stdin2, stdout2, stderr2 = client.exec_command(cmd2)
print("Worker Result:", stdout2.read().decode('utf-8', errors='replace'))
print("Worker Error:", stderr2.read().decode('utf-8', errors='replace'))

client.close()
