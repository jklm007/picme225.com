import paramiko

HOSTNAME = '109.199.123.69'
USERNAME = 'root'
PASSWORD = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOSTNAME, username=USERNAME, password=PASSWORD, timeout=30)

stdin, stdout, stderr = client.exec_command("kubectl get pods -l app=laravel --no-headers -o custom-columns=NAME:.metadata.name | head -n 1")
pod = stdout.read().decode().strip()
print(f"Pod: {pod}")

# 1. Temporarily enable debug
stdin, stdout, stderr = client.exec_command(
    f"kubectl exec {pod} -- bash -c \"sed -i 's/APP_DEBUG=false/APP_DEBUG=true/' /app/.env && php artisan config:clear\""
)
print("Debug enabled:", stdout.read().decode(), stderr.read().decode())

# 2. Now hit the homepage to see the real error
stdin, stdout, stderr = client.exec_command(
    f"kubectl exec {pod} -- curl -s http://127.0.0.1/ 2>&1 | head -c 3000"
)
page = stdout.read().decode('utf-8', errors='replace')
with open('scratch/homepage_debug.txt', 'w', encoding='utf-8') as f:
    f.write(page)
print(f"Homepage with debug:\n{page[:2000]}")

# 3. Check storage/logs after the request
stdin, stdout, stderr = client.exec_command(
    f"kubectl exec {pod} -- bash -c 'ls -la /app/storage/logs/ 2>&1 && tail -50 /app/storage/logs/laravel.log 2>/dev/null'"
)
logs = stdout.read().decode('utf-8', errors='replace')
print(f"Storage logs:\n{logs}")

# 4. Restore debug=false
stdin, stdout, stderr = client.exec_command(
    f"kubectl exec {pod} -- bash -c \"sed -i 's/APP_DEBUG=true/APP_DEBUG=false/' /app/.env && php artisan config:clear\""
)
print("Debug disabled:", stdout.read().decode())

client.close()
