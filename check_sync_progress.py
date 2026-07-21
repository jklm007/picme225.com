import paramiko

hostname = '109.199.123.69'
username = 'root'
password = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(hostname, username=username, password=password)

# Check evolution-api logs to see if synchronization has progressed
cmd = """kubectl logs --since=2m deploy/evolution-api-deployment | grep -E "progress" || true"""
stdin, stdout, stderr = client.exec_command(cmd)
out = stdout.read().decode('utf-8', errors='replace')

client.close()

print("SYNC PROGRESS LOGS:")
print(out)
