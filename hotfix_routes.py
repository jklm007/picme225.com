import paramiko
import base64

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

with open(r'routes\web.php', 'rb') as f:
    web_php_b64 = base64.b64encode(f.read()).decode('utf-8')

commands = f"""
set -e
echo "{web_php_b64}" | base64 -d > /tmp/web.php

POD=$(kubectl get pods -l app=laravel -o jsonpath="{{.items[0].metadata.name}}")
WORKER_POD=$(kubectl get pods -l app=laravel-worker -o jsonpath="{{.items[0].metadata.name}}")

echo "Copying web.php to Laravel Web Pod ($POD)..."
kubectl cp /tmp/web.php $POD:/app/routes/web.php

echo "Copying web.php to Laravel Worker Pod ($WORKER_POD)..."
kubectl cp /tmp/web.php $WORKER_POD:/app/routes/web.php

echo "Clearing route cache..."
kubectl exec $POD -- php artisan route:clear
kubectl exec $WORKER_POD -- php artisan route:clear
"""

stdin, stdout, stderr = client.exec_command(commands)
print(stdout.read().decode())
print("ERR:", stderr.read().decode())
client.close()
