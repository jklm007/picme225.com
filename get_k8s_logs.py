import paramiko, sys

hostname = '109.199.123.69'
username = 'root'
password = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(hostname, username=username, password=password)

# Get the last 200 lines from the laravel-deployment pods
cmd = """kubectl logs --tail=200 -l app=laravel"""
stdin, stdout, stderr = client.exec_command(cmd)
laravel_out = stdout.read().decode('utf-8', errors='replace')

# Get the last 200 lines from the laravel-worker pods
cmd2 = """kubectl logs --tail=200 -l app=laravel-worker"""
stdin2, stdout2, stderr2 = client.exec_command(cmd2)
worker_out = stdout2.read().decode('utf-8', errors='replace')

client.close()

# Write both to files
with open("k8s_laravel.log", "w", encoding="utf-8") as f:
    f.write(laravel_out)

with open("k8s_worker.log", "w", encoding="utf-8") as f:
    f.write(worker_out)

print("Logs written successfully to k8s_laravel.log and k8s_worker.log")
