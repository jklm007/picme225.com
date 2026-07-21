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

$queries = [
    '9b69466e',
    'd46be1d8',
    '38762fb3',
    'd7376acc',
    '3dd1c8fe',
    '8be45e4d',
    '0b92a9b5',
    'd112bab1',
    '9a86c61b',
    'd580a95f'
];

foreach ($queries as $q) {
    $found = false;
    foreach ($files as $file) {
        if (strpos($file, $q) !== false) {
            echo "Query {$q} found: {$file}\\n";
            $found = true;
        }
    }
    if (!$found) {
        echo "Query {$q} NOT found\\n";
    }
}
"""

encoded = base64.b64encode(php_script.encode()).decode()
command = f"kubectl exec {pod} -- bash -c \"echo '{encoded}' | base64 -d > /app/public/test_search_all.php && php /app/public/test_search_all.php\""
stdin, stdout, stderr = client.exec_command(command)
print(stdout.read().decode())
print(stderr.read().decode())
