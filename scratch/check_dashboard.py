import paramiko, os

HOSTNAME = '109.199.123.69'
USERNAME = 'root'
PASSWORD = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOSTNAME, username=USERNAME, password=PASSWORD, timeout=30)

def run(cmd, label=""):
    stdin, stdout, stderr = client.exec_command(cmd)
    out = stdout.read().decode('utf-8', errors='replace').strip()
    err = stderr.read().decode('utf-8', errors='replace').strip()
    if label: print(f"[{label}] {out or err}")
    return out

pod = run("kubectl get pods -l app=laravel --no-headers -o custom-columns=NAME:.metadata.name | head -n 1")
print(f"Pod: {pod}")

# Check key files on server vs local
files = [
    ('resources/views/user/dashboard.blade.php', '/app/resources/views/user/dashboard.blade.php', 'Dashboard'),
    ('resources/views/home.blade.php',            '/app/resources/views/home.blade.php',            'Home Landing'),
    ('resources/views/user/layout/app.blade.php', '/app/resources/views/user/layout/app.blade.php', 'App Layout'),
    ('resources/views/user/layout/base.blade.php','/app/resources/views/user/layout/base.blade.php','Base Layout'),
]

print("\n=== File sizes comparison (local vs server) ===")
for local, remote, label in files:
    local_size = os.path.getsize(local) if os.path.exists(local) else 0
    server_size = run(f"kubectl exec {pod} -- wc -c {remote} | awk '{{print $1}}'")
    status = "OK" if local_size > 0 and server_size == str(local_size) else ("MISMATCH" if server_size != str(local_size) else "EMPTY")
    print(f"  [{status}] {label}: local={local_size}b, server={server_size}b")

# Check if dashboard route redirects correctly for a guest
print("\n=== Route check ===")
route = run(f"kubectl exec {pod} -- php artisan route:list --path=dashboard 2>&1 | grep -v WARNING | head -5")
print(f"Dashboard routes: {route}")

# Test the dashboard page
dashboard_size = run(f"kubectl exec {pod} -- curl -s -H 'Host: picme225.site' http://127.0.0.1/account/dashboard | wc -c")
print(f"Dashboard page size: {dashboard_size}b")

client.close()
