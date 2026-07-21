import paramiko
import base64

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

stdin, stdout, stderr = client.exec_command("kubectl get pod -l app=laravel -o jsonpath='{.items[0].metadata.name}'")
pod = stdout.read().decode().strip()

php_script = """<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\\Contracts\\Console\\Kernel::class);
$kernel->bootstrap();
echo 'r2_access_key: ' . \\Setting::get('r2_access_key') . "\\n";
echo 'r2_url: ' . \\Setting::get('r2_url') . "\\n";
"""

encoded = base64.b64encode(php_script.encode()).decode()
command = f"kubectl exec {pod} -- bash -c \"echo '{encoded}' | base64 -d > /tmp/check.php && php /tmp/check.php\""
stdin, stdout, stderr = client.exec_command(command)
print(stdout.read().decode())
print(stderr.read().decode())
