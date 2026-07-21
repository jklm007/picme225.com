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

echo "=== SERVICES ===\n";
foreach (\App\Models\Service::all() as $s) {
    echo "ID: {$s->id} | Name: {$s->name} | Status: {$s->status} | ServiceTypes count: " . $s->serviceTypes()->count() . "\n";
}

echo "\n=== SERVICE TYPES ===\n";
foreach (\App\Models\ServiceType::all() as $st) {
    echo "ID: {$st->id} | Name: {$st->name} | Status: {$st->status}\n";
}
"""

sftp = client.open_sftp()
sftp.putfo(io.BytesIO(php), '/tmp/db_check.php')
sftp.close()

client.exec_command(f"kubectl cp /tmp/db_check.php {pod}:/tmp/db_check.php")
stdin, stdout, stderr = client.exec_command(f"kubectl exec {pod} -- php /tmp/db_check.php")
print(stdout.read().decode('utf-8'))
print(stderr.read().decode('utf-8'))

client.close()
