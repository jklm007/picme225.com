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

echo 'default_disk: ' . config('filesystems.default') . "\\n";
echo 's3_url_config: ' . config('filesystems.disks.s3.url') . "\\n";
echo 's3_endpoint_config: ' . config('filesystems.disks.s3.endpoint') . "\\n";
echo 'test_url: ' . \\Illuminate\\Support\\Facades\\Storage::disk(config('filesystems.default'))->url('listings/test.webp') . "\\n";
"""

encoded = base64.b64encode(php_script.encode()).decode()
command = f"kubectl exec {pod} -- bash -c \"echo '{encoded}' | base64 -d > /app/public/test_url.php && php /app/public/test_url.php\""
stdin, stdout, stderr = client.exec_command(command)
print(stdout.read().decode())
print(stderr.read().decode())
