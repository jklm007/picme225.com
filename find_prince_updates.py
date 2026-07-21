import paramiko

hostname = '109.199.123.69'
username = 'root'
password = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(hostname, username=username, password=password)

# Check all webhook updates related to Prince's number 22577436121
cmd = """kubectl logs --tail=20000 deploy/laravel-deployment | grep "22577436121" || true"""
stdin, stdout, stderr = client.exec_command(cmd)
out = stdout.read().decode('utf-8', errors='replace')

client.close()

with open("prince_updates.log", "w", encoding="utf-8") as f:
    f.write(out)

print("Prince updates written to prince_updates.log")
