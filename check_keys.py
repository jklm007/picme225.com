import paramiko

hostname = '109.199.123.69'
username = 'root'
password = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(hostname, username=username, password=password)

# Check if oauth keys exist inside the laravel container
cmd = """kubectl exec deploy/laravel-deployment -- ls -la storage/"""
stdin, stdout, stderr = client.exec_command(cmd)
print("STORAGE DIR:")
print(stdout.read().decode('utf-8', errors='replace'))
print("STDERR:", stderr.read().decode('utf-8', errors='replace'))

client.close()
