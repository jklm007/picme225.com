import paramiko
import base64

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

with open(r'resources\views\user\auth\login.blade.php', 'rb') as f:
    login_b64 = base64.b64encode(f.read()).decode('utf-8')

with open(r'resources\views\marketing\layout.blade.php', 'rb') as f:
    layout_b64 = base64.b64encode(f.read()).decode('utf-8')

commands = f"""
set -e
echo "{login_b64}" | base64 -d > /tmp/login.blade.php
echo "{layout_b64}" | base64 -d > /tmp/layout.blade.php

POD=$(kubectl get pods -l app=laravel -o jsonpath="{{.items[0].metadata.name}}")
WORKER_POD=$(kubectl get pods -l app=laravel-worker -o jsonpath="{{.items[0].metadata.name}}")

echo "Copying to Laravel Web Pod ($POD)..."
kubectl cp /tmp/login.blade.php $POD:/app/resources/views/user/auth/login.blade.php
kubectl cp /tmp/layout.blade.php $POD:/app/resources/views/marketing/layout.blade.php

echo "Copying to Laravel Worker Pod ($WORKER_POD)..."
kubectl cp /tmp/login.blade.php $WORKER_POD:/app/resources/views/user/auth/login.blade.php
kubectl cp /tmp/layout.blade.php $WORKER_POD:/app/resources/views/marketing/layout.blade.php

echo "Clearing view cache..."
kubectl exec $POD -- php artisan view:clear
kubectl exec $WORKER_POD -- php artisan view:clear
"""

stdin, stdout, stderr = client.exec_command(commands)
print(stdout.read().decode())
print("ERR:", stderr.read().decode())
client.close()
