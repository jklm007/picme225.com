import paramiko

hostname = '109.199.123.69'
username = 'root'
password = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(hostname, username=username, password=password)

# Fetch evolution-api logs around 19:11 to see the prekey/encryption error details for 2250714950219
cmd = """kubectl logs --since=10m deploy/evolution-api-deployment | grep -i -C 5 -E "2250714950219|3EB00E520B652F6A79C6AF" || true"""
stdin, stdout, stderr = client.exec_command(cmd)
out = stdout.read().decode('utf-8', errors='replace')

client.close()

with open("son_evo_logs.txt", "w", encoding="utf-8") as f:
    f.write(out)

print("Logs written to son_evo_logs.txt")
