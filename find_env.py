import paramiko

hostname = '109.199.123.69'
username = 'root'
password = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(hostname, username=username, password=password)

# Locate the .env file on the server's local file system
cmd = """find / -name ".env" -not -path "*/node_modules/*" -not -path "*/vendor/*" -not -path "*/var/lib/*" 2>/dev/null || true"""
stdin, stdout, stderr = client.exec_command(cmd)
print("LOCATED .ENV FILES:")
print(stdout.read().decode('utf-8', errors='replace'))

# Check K8s secrets
cmd2 = """kubectl get secrets"""
stdin2, stdout2, stderr2 = client.exec_command(cmd2)
print("K8S SECRETS:")
print(stdout2.read().decode('utf-8', errors='replace'))

client.close()
