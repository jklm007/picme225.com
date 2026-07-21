import paramiko, io

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

stdin, stdout, stderr = client.exec_command("kubectl get pod -l app=laravel -o jsonpath='{.items[0].metadata.name}'")
pod = stdout.read().decode().strip().strip("'")

php = b"""<?php
require '/app/vendor/autoload.php';
$app = require '/app/bootstrap/app.php';
$kernel = $app->make(Illuminate\\Contracts\\Console\\Kernel::class);
$kernel->bootstrap();

$count = \\App\\Models\\Service::count();
echo "Total categories: $count\\n";

$svc = \\App\\Models\\Service::first();
if ($svc) {
    echo "First image URL: " . $svc->image_url . "\\n";
} else {
    echo "No categories found!\\n";
}
"""

sftp = client.open_sftp()
sftp.putfo(io.BytesIO(php), '/tmp/test_images.php')
sftp.close()

client.exec_command(f"kubectl cp /tmp/test_images.php {pod}:/tmp/test_images.php")
stdin, stdout, stderr = client.exec_command(f"kubectl exec {pod} -- php /tmp/test_images.php")
print(stdout.read().decode('utf-8'))
print(stderr.read().decode('utf-8'))

client.close()
