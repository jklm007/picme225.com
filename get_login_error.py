import paramiko

hostname = '109.199.123.69'
username = 'root'
password = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(hostname, username=username, password=password)

# Fetch latest logs from laravel deployment (both stdout and stderr if any)
cmd = """kubectl logs --since=10m deploy/laravel-deployment | tail -n 200"""
stdin, stdout, stderr = client.exec_command(cmd)
out = stdout.read().decode('utf-8', errors='replace')

client.close()

with open("laravel_login_error.log", "w", encoding="utf-8") as f:
    f.write(out)

print("Logs written to laravel_login_error.log")
