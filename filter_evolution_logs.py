import paramiko

hostname = '109.199.123.69'
username = 'root'
password = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(hostname, username=username, password=password)

# Check evolution-api logs filtering for 58286571 or 77436121
cmd = """kubectl logs --tail=2000 deploy/evolution-api-deployment | grep -E "58286571|77436121|error|fail" | tail -n 100"""
stdin, stdout, stderr = client.exec_command(cmd)
out = stdout.read().decode('utf-8', errors='replace')
err = stderr.read().decode('utf-8', errors='replace')

client.close()

print("FILTERED EVOLUTION LOGS:")
print(out)
if err: print("STDERR:", err)
