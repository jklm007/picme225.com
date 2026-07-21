import paramiko

hostname = '109.199.123.69'
username = 'root'
password = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(hostname, username=username, password=password)

# Fetch deployment YAML for laravel-worker
cmd = """kubectl get deploy laravel-worker -o yaml"""
stdin, stdout, stderr = client.exec_command(cmd)
out = stdout.read().decode('utf-8', errors='replace')

client.close()

with open("laravel_worker_deployment.yaml", "w", encoding="utf-8") as f:
    f.write(out)

print("Worker YAML written to laravel_worker_deployment.yaml")
