import paramiko
import os

HOSTNAME = '109.199.123.69'
USERNAME = 'root'
PASSWORD = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOSTNAME, username=USERNAME, password=PASSWORD, timeout=30)

stdin, stdout, stderr = client.exec_command("kubectl get pods -l app=laravel --no-headers -o custom-columns=NAME:.metadata.name | head -n 1")
pod = stdout.read().decode().strip()
print(f"Pod: {pod}")

# 1. Check if php artisan can run
stdin, stdout, stderr = client.exec_command(f"kubectl exec {pod} -- php artisan --version 2>&1")
out = stdout.read().decode()
err = stderr.read().decode()
print(f"PHP Artisan: {out} | {err}")

# 2. Check env APP_DEBUG
stdin, stdout, stderr = client.exec_command(f"kubectl exec {pod} -- grep -E 'APP_DEBUG|APP_ENV|APP_KEY' /app/.env 2>&1 | head -5")
env_out = stdout.read().decode()
print(f".env snippet:\n{env_out}")

# 3. Do a real curl hit on the homepage
stdin, stdout, stderr = client.exec_command(f"kubectl exec {pod} -- curl -s http://127.0.0.1/index.php 2>&1 | head -c 2000")
page = stdout.read().decode('utf-8', errors='replace')
with open('scratch/homepage_raw.txt', 'w', encoding='utf-8') as f:
    f.write(page)
print(f"Homepage start:\n{page[:1000]}")

# 4. Check PHP-FPM logs
stdin, stdout, stderr = client.exec_command(f"kubectl exec {pod} -- bash -c 'find /var/log -name \"*.log\" 2>/dev/null | head -10'")
print("Logs:", stdout.read().decode())

client.close()
