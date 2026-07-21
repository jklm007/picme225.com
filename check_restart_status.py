import paramiko

hostname = '109.199.123.69'
username = 'root'
password = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(hostname, username=username, password=password)

# Check laravel logs for the message ID 3EB09D15783AF6356474DC
cmd = """kubectl logs --tail=100 deploy/laravel-deployment | grep "3EB09D15783AF6356474DC" || true"""
stdin, stdout, stderr = client.exec_command(cmd)
out = stdout.read().decode('utf-8', errors='replace')

client.close()

print("STATUS LOGS:")
print(out)
