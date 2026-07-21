import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23', timeout=15)

# Simple approach: use artisan to run a raw query via shell script
php_code = "<?php " + """
define('LARAVEL_START', microtime(true));
require '/app/vendor/autoload.php';
$app = require '/app/bootstrap/app.php';
$kernel = $app->make(Illuminate\\Contracts\\Console\\Kernel::class);
$kernel->bootstrap();
// Services
$services = DB::table('services')->select('id','name','image')->limit(10)->get();
echo "=== SERVICES ===\\n";
foreach($services as $r) {
    echo $r->id.'|'.$r->name.'|'.$r->image."\\n";
}
// ServiceTypes  
$types = DB::table('service_types')->select('id','name','image')->limit(10)->get();
echo "\\n=== SERVICE_TYPES ===\\n";
foreach($types as $r) {
    echo $r->id.'|'.$r->name.'|'.$r->image."\\n";
}
"""

# Write to pod
sftp = client.open_sftp()
with sftp.open('/tmp/check_db.php', 'w') as f:
    f.write(php_code)
sftp.close()

stdin, stdout, stderr = client.exec_command('kubectl cp /tmp/check_db.php laravel-deployment-56f54497f-r8pmg:/tmp/check_db.php')
stdout.read()

stdin2, stdout2, stderr2 = client.exec_command('kubectl exec laravel-deployment-56f54497f-r8pmg -- php /tmp/check_db.php')
out = stdout2.read().decode('utf-8', errors='ignore').strip()
err = stderr2.read().decode('utf-8', errors='ignore').strip()
print("OUT:\n" + out)
if err:
    print("ERR:\n" + err[:500])

client.close()
