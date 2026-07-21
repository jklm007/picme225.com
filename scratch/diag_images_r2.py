import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23', timeout=15)

php_script = """<?php
require '/app/vendor/autoload.php';
$app = require '/app/bootstrap/app.php';
$kernel = $app->make(Illuminate\\Contracts\\Console\\Kernel::class);
$kernel->bootstrap();

$s = App\\Models\\Service::first();
echo 'raw: '.$s->image."\\n";
echo 'url: '.$s->image_url."\\n";

$t = App\\Models\\ServiceType::first();
if ($t) {
    echo 'type_raw: '.$t->image."\\n";
    echo 'type_url: '.$t->image_url."\\n";
}
"""

print("=== Uploading script ===")
cmd_upload = f"kubectl exec laravel-deployment-56f54497f-r8pmg -- bash -c \"cat > /tmp/diag.php << 'EOF'\n{php_script}\nEOF\""
client.exec_command(cmd_upload)

print("=== Running script ===")
cmd_run = "kubectl exec laravel-deployment-56f54497f-r8pmg -- php /tmp/diag.php"
stdin, stdout, stderr = client.exec_command(cmd_run)
out = stdout.read().decode('utf-8', errors='ignore').strip()
err = stderr.read().decode('utf-8', errors='ignore').strip()
print("OUT:", out)
if err: print("ERR:", err)

client.close()
