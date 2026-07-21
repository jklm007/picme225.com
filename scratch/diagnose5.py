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

# 1. Get actual HTML body of homepage (with correct Host header)
stdin, stdout, stderr = client.exec_command(
    f"kubectl exec {pod} -- curl -s -H 'Host: picme225.site' http://127.0.0.1/ 2>&1 | head -c 5000"
)
html = stdout.read().decode('utf-8', errors='replace')
with open('scratch/homepage_body.html', 'w', encoding='utf-8') as f:
    f.write(html)
print("HTML snippet:", html[:3000])

# 2. Check if home.blade.php syntax is OK via PHP parse check
stdin, stdout, stderr = client.exec_command(
    f"kubectl exec {pod} -- php -l /app/resources/views/home.blade.php 2>&1"
)
print("\nPHP lint home:", stdout.read().decode('utf-8', errors='replace'))

# 3. Check compiled views
stdin, stdout, stderr = client.exec_command(
    f"kubectl exec {pod} -- bash -c 'ls /app/storage/framework/views/ 2>&1 | head -5'"
)
print("Compiled views:", stdout.read().decode('utf-8', errors='replace'))

# 4. Try artisan route:list for home
stdin, stdout, stderr = client.exec_command(
    f"kubectl exec {pod} -- php artisan route:list --name=home 2>&1 | head -20"
)
print("Route list:", stdout.read().decode('utf-8', errors='replace'))

client.close()
