import paramiko
import base64

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

def get_b64(path):
    with open(path, 'rb') as f:
        return base64.b64encode(f.read()).decode('utf-8')

user_login_ctrl = get_b64(r'app\Http\Controllers\Auth\LoginController.php')
prov_login_ctrl = get_b64(r'app\Http\Controllers\ProviderAuth\LoginController.php')
login_blade = get_b64(r'resources\views\user\auth\login.blade.php')
register_blade = get_b64(r'resources\views\user\auth\register.blade.php')

commands = f"""
set -e
echo "{user_login_ctrl}" | base64 -d > /tmp/UserLoginController.php
echo "{prov_login_ctrl}" | base64 -d > /tmp/ProvLoginController.php
echo "{login_blade}" | base64 -d > /tmp/login.blade.php
echo "{register_blade}" | base64 -d > /tmp/register.blade.php

POD=$(kubectl get pods -l app=laravel -o jsonpath="{{.items[0].metadata.name}}")
WORKER_POD=$(kubectl get pods -l app=laravel-worker -o jsonpath="{{.items[0].metadata.name}}")

for P in $POD $WORKER_POD; do
    echo "Copying to $P..."
    kubectl cp /tmp/UserLoginController.php $P:/app/app/Http/Controllers/Auth/LoginController.php
    kubectl cp /tmp/ProvLoginController.php $P:/app/app/Http/Controllers/ProviderAuth/LoginController.php
    kubectl cp /tmp/login.blade.php $P:/app/resources/views/user/auth/login.blade.php
    kubectl cp /tmp/register.blade.php $P:/app/resources/views/user/auth/register.blade.php
    kubectl exec $P -- php artisan view:clear
done
"""

stdin, stdout, stderr = client.exec_command(commands)
print(stdout.read().decode())
print("ERR:", stderr.read().decode())
client.close()
