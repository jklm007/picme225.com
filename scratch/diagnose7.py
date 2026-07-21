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

cmds = [
    # Check the actual vhost config
    ("vhost config", f"kubectl exec {pod} -- cat /opt/docker/etc/nginx/vhost.conf 2>&1 | head -50"),
    # Check global config
    ("global config", f"kubectl exec {pod} -- cat /opt/docker/etc/nginx/global.conf 2>&1 | head -30"),
    # Check the php config
    ("php config", f"kubectl exec {pod} -- cat /opt/docker/etc/nginx/php.conf 2>&1 | head -30"),
    # Save response to file on pod and check its size
    ("save & check", f"kubectl exec {pod} -- bash -c 'curl -s -H \"Host: picme225.site\" http://127.0.0.1/ > /tmp/page.html 2>&1; wc -c /tmp/page.html; head -c 500 /tmp/page.html'"),
    # Test from outside the pod using server's curl
    ("external curl", "curl -s -o /dev/null -w '%{http_code}' https://picme225.site/ 2>&1"),
    # Check HomeController
    ("HomeController exists", f"kubectl exec {pod} -- ls -la /app/app/Http/Controllers/HomeController.php"),
    # PHP-FPM status
    ("php-fpm pool", f"kubectl exec {pod} -- cat /opt/docker/etc/php-fpm.d/application.conf 2>/dev/null | head -20 || kubectl exec {pod} -- cat /etc/php-fpm.d/*.conf 2>/dev/null | head -20"),
]

for name, cmd in cmds:
    print(f"\n{'='*40}")
    print(f">>> {name}")
    stdin, stdout, stderr = client.exec_command(cmd)
    out = stdout.read().decode('utf-8', errors='replace')
    err = stderr.read().decode('utf-8', errors='replace')
    print(out[:2000] if out else "(empty)")
    if err and err.strip():
        print(f"STDERR: {err[:500]}")

client.close()
