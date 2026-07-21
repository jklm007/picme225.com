import paramiko

hostname = '109.199.123.69'
username = 'root'
password = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(hostname, username=username, password=password)

# Check all webhook updates related to the LID of the wife 195923657408723
cmd = """kubectl logs --tail=10000 deploy/laravel-deployment | grep "195923657408723" || true"""
stdin, stdout, stderr = client.exec_command(cmd)
out = stdout.read().decode('utf-8', errors='replace')

client.close()

with open("lid_updates.log", "w", encoding="utf-8") as f:
    f.write(out)

print("LID updates written to lid_updates.log")
