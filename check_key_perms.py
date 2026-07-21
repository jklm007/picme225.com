import paramiko

hostname = '109.199.123.69'
username = 'root'
password = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(hostname, username=username, password=password)

# Check details of storage directory and oauth keys in the pod
cmd = """kubectl exec deploy/laravel-deployment -- ls -la storage/"""
stdin, stdout, stderr = client.exec_command(cmd)
print("STORAGE FILES:")
print(stdout.read().decode('utf-8', errors='replace'))

client.close()
