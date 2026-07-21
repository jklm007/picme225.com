import paramiko, time

hostname = '109.199.123.69'
username = 'root'
password = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(hostname, username=username, password=password)

# Wait 10 seconds
print("Waiting 10 seconds...")
time.sleep(10)

# Check evolution-api logs for sync progress
cmd = """kubectl logs --since=2m deploy/evolution-api-deployment | grep -E "progress|latest|sync" | tail -n 10 || true"""
stdin, stdout, stderr = client.exec_command(cmd)
out = stdout.read().decode('utf-8', errors='replace')

client.close()

print("SYNC PROGRESS:")
print(out)
