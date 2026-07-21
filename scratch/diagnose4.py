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
    # Check nginx config
    ("nginx config", "kubectl exec {pod} -- cat /etc/nginx/sites-enabled/default 2>/dev/null || kubectl exec {pod} -- cat /etc/nginx/conf.d/default.conf 2>/dev/null | head -50"),
    # Check if nginx is actually running
    ("nginx status", "kubectl exec {pod} -- ps aux | grep -E 'nginx|php'"),
    # Check listening ports
    ("ports", "kubectl exec {pod} -- ss -tlnp 2>/dev/null || kubectl exec {pod} -- netstat -tlnp 2>/dev/null"),
    # Check storage perms
    ("storage perms", "kubectl exec {pod} -- ls -la /app/storage/"),
    # Make storage writable
    ("chmod storage", "kubectl exec {pod} -- chmod -R 777 /app/storage /app/bootstrap/cache 2>&1"),
    # Try PHP directly 
    ("php test", "kubectl exec {pod} -- php -r \"echo 'PHP OK: ' . phpversion();\""),
    # Check public/index.php exists
    ("index.php", "kubectl exec {pod} -- ls -la /app/public/index.php"),
    # Curl with verbose
    ("curl verbose", "kubectl exec {pod} -- curl -v http://127.0.0.1/ 2>&1 | head -50"),
]

for name, cmd in cmds:
    cmd = cmd.replace('{pod}', pod)
    print(f"\n{'='*40}")
    print(f">>> {name}")
    stdin, stdout, stderr = client.exec_command(cmd)
    out = stdout.read().decode('utf-8', errors='replace')
    err = stderr.read().decode('utf-8', errors='replace')
    print(out[:1000] if out else "(empty)")
    if err:
        print("STDERR:", err[:500])

client.close()
