import paramiko

HOSTNAME = '109.199.123.69'
USERNAME = 'root'
PASSWORD = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOSTNAME, username=USERNAME, password=PASSWORD, timeout=10)

stdin, stdout, stderr = client.exec_command("kubectl get pods -l app=laravel --no-headers -o custom-columns=NAME:.metadata.name | head -n 1")
pod = stdout.read().decode().strip()
print(f"Pod: {pod}")

# Get the latest laravel errors directly from within the pod
stdin, stdout, stderr = client.exec_command(f"kubectl exec {pod} -- bash -c \"find /app/storage/logs -name '*.log' 2>/dev/null\"")
log_files = stdout.read().decode().strip()
print(f"Log files: {log_files}")

# Try to get errors from php-fpm error log or nginx
stdin, stdout, stderr = client.exec_command(f"kubectl exec {pod} -- bash -c \"cat /var/log/nginx/error.log 2>/dev/null | tail -50\"")
nginx_err = stdout.read().decode()
print("NGINX ERR:", nginx_err or "(empty)")

# Simulate a request to homepage and see if there is a PHP error
stdin, stdout, stderr = client.exec_command(f"kubectl exec {pod} -- bash -c \"curl -s -o /tmp/home.html -w '%{{http_code}}' http://localhost/ 2>&1 && head -200 /tmp/home.html\"")
with open('scratch/homepage_response.html', 'wb') as f:
    f.write(stdout.read())
print(stderr.read().decode())
