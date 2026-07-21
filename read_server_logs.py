import paramiko, json

hostname = '109.199.123.69'
username = 'root'
password = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(hostname, username=username, password=password)

# Check laravel logs for the string "58286571" or "77436121"
cmd = """kubectl exec deploy/laravel-deployment -- tail -n 100 storage/logs/laravel.log"""
stdin, stdout, stderr = client.exec_command(cmd)
out = stdout.read().decode('utf-8', errors='replace')
err = stderr.read().decode('utf-8', errors='replace')
print("LARAVEL LOGS:")
print(out)
if err: print("ERR:", err)

client.close()
