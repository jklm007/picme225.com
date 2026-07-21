import paramiko

hostname = '109.199.123.69'
username = 'root'
password = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(hostname, username=username, password=password)

# Fetch all logs for evolution-api from the last 15 minutes
cmd = """kubectl logs --since=15m deploy/evolution-api-deployment"""
stdin, stdout, stderr = client.exec_command(cmd)
out = stdout.read().decode('utf-8', errors='replace')

client.close()

with open("evolution_api_15m.log", "w", encoding="utf-8") as f:
    f.write(out)

print("Logs written to evolution_api_15m.log")
