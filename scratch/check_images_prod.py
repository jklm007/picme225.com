import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23', timeout=15)

php_script = """
require '/app/vendor/autoload.php';
$app = require '/app/bootstrap/app.php';
$kernel = $app->make(Illuminate\\Contracts\\Console\\Kernel::class);
$kernel->bootstrap();

$s = App\\Models\\Service::first();
if ($s) {
    echo 'service image: ' . $s->image . "\\n";
}
$t = App\\Models\\ServiceType::first();
if ($t) {
    echo 'servicetype image: ' . $t->image . "\\n";
}
"""

cmd_upload = f"kubectl exec laravel-deployment-56f54497f-r8pmg -- bash -c \"cat > /tmp/check_img.php << 'EOF'\n<?php\n{php_script}\nEOF\""
client.exec_command(cmd_upload)

cmd_run = "kubectl exec laravel-deployment-56f54497f-r8pmg -- php /tmp/check_img.php"
stdin, stdout, stderr = client.exec_command(cmd_run)
out = stdout.read().decode('utf-8', errors='ignore').strip()
err = stderr.read().decode('utf-8', errors='ignore').strip()
print("OUT:\n" + out)
if err: print("ERR:\n" + err)

client.close()
