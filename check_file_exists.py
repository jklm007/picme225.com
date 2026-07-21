import paramiko
import base64

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

stdin, stdout, stderr = client.exec_command("kubectl get pod -l app=laravel -o jsonpath='{.items[0].metadata.name}'")
pod = stdout.read().decode().strip()

php_script = """<?php
require '/app/vendor/autoload.php';
$app = require_once '/app/bootstrap/app.php';
$kernel = $app->make(Illuminate\\Contracts\\Console\\Kernel::class);
$kernel->bootstrap();

$disk = \\Illuminate\\Support\\Facades\\Storage::disk('s3');
$path1 = 'listings/9b69466e-1dc1-4dcb-8a44-c2a7a7a0dbd4_1784298267.webp';
$path2 = 'marketplace/listings/9b69466e-1dc1-4dcb-8a44-c2a7a7a0dbd4_1784298267.webp';

echo "path1 exists: " . ($disk->exists($path1) ? 'YES' : 'NO') . "\\n";
echo "path2 exists: " . ($disk->exists($path2) ? 'YES' : 'NO') . "\\n";
"""

encoded = base64.b64encode(php_script.encode()).decode()
command = f"kubectl exec {pod} -- bash -c \"echo '{encoded}' | base64 -d > /app/public/test_exists.php && php /app/public/test_exists.php\""
stdin, stdout, stderr = client.exec_command(command)
print(stdout.read().decode())
print(stderr.read().decode())
