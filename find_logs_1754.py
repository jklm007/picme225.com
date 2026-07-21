import paramiko

hostname = '109.199.123.69'
username = 'root'
password = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(hostname, username=username, password=password)

# Check all laravel logs containing 195923657408723 or around 17:54
cmd = """kubectl logs --tail=20000 deploy/laravel-deployment | grep -i -C 2 -E "17:54|195923657408723" | tail -n 100"""
stdin, stdout, stderr = client.exec_command(cmd)
out = stdout.read().decode('utf-8', errors='replace')

client.close()

with open("logs_1754.log", "w", encoding="utf-8") as f:
    f.write(out)

print("Logs written to logs_1754.log")
