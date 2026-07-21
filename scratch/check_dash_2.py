import paramiko

HOSTNAME = '109.199.123.69'
USERNAME = 'root'
PASSWORD = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOSTNAME, username=USERNAME, password=PASSWORD, timeout=30)

def run(cmd):
    stdin, stdout, stderr = client.exec_command(cmd)
    return stdout.read().decode('utf-8', errors='replace').strip()

pod = run("kubectl get pods -l app=laravel --no-headers -o custom-columns=NAME:.metadata.name | head -n 1")

# Try to find all dashboard-related views
dash_views = run(f"kubectl exec {pod} -- find /app/resources/views -name '*dashboard*.blade.php'")
print(f"Dashboard views:\n{dash_views}")

# Clear view cache again forcefully
run(f"kubectl exec {pod} -- rm -rf /app/storage/framework/views/*.php")
print("Cleared compiled views manually.")

# Let's check which view is actually returned for the /account/dashboard route by looking at the controller
route_action = run(f"kubectl exec {pod} -- php artisan route:list --path=dashboard 2>&1 | grep 'account/dashboard'")
print(f"Route action:\n{route_action}")

client.close()
