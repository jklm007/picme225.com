import paramiko

hostname = '109.199.123.69'
username = 'root'
password = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(hostname, username=username, password=password)

# Check evolution-api logs filtering for errors, failures, sendText, status
cmd = """kubectl logs --tail=5000 deploy/evolution-api-deployment | grep -i -C 3 -E "error|fail|reject|invalid|warn|sendText|status" | tail -n 100"""
stdin, stdout, stderr = client.exec_command(cmd)
out = stdout.read().decode('utf-8', errors='replace')

client.close()

with open("evo_errors.txt", "w", encoding="utf-8") as f:
    f.write(out)

print("Errors written to evo_errors.txt")
