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
$files = $disk->allFiles();
echo "Total files on R2: " . count($files) . "\\n";
foreach ($files as $file) {
    if (strpos($file, '9b69466e') !== false) {
        echo "Found: " . $file . "\\n";
    }
}
"""

encoded = base64.b64encode(php_script.encode()).decode()
command = f"kubectl exec {pod} -- bash -c \"echo '{encoded}' | base64 -d > /app/public/test_search.php && php /app/public/test_search.php\""
stdin, stdout, stderr = client.exec_command(command)
print(stdout.read().decode())
print(stderr.read().decode())
