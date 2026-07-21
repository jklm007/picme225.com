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

# Check what controller handles '/'
stdin, stdout, stderr = client.exec_command(
    f"kubectl exec {pod} -- php artisan route:list --path=/ 2>&1 | grep -v WARNING | head -20"
)
routes = stdout.read().decode('utf-8', errors='replace')
print(f"Routes for /:\n{routes}")

# Check nginx config
stdin, stdout, stderr = client.exec_command(
    f"kubectl exec {pod} -- bash -c 'cat /etc/nginx/conf.d/*.conf 2>/dev/null || cat /etc/nginx/sites-enabled/* 2>/dev/null || cat /etc/nginx/nginx.conf' | head -80"
)
nginx = stdout.read().decode('utf-8', errors='replace')
print(f"Nginx config:\n{nginx}")

# Check php-fpm socket
stdin, stdout, stderr = client.exec_command(
    f"kubectl exec {pod} -- bash -c 'ls -la /var/run/php* 2>/dev/null || ls -la /run/php* 2>/dev/null'"
)
sockets = stdout.read().decode('utf-8', errors='replace')
print(f"PHP sockets: {sockets}")

# Try calling Laravel directly via PHP CLI
stdin, stdout, stderr = client.exec_command(
    f"kubectl exec {pod} -- bash -c 'cd /app && php artisan tinker --execute=\"echo app()->version();\" 2>&1 | head -5'"
)
tinker = stdout.read().decode('utf-8', errors='replace')
print(f"Tinker test: {tinker}")

client.close()
