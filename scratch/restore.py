import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

stdin, stdout, stderr = client.exec_command("kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}'")
pod = stdout.read().decode().strip().strip("'")

php_script = """
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\\Contracts\\Console\\Kernel::class);
$kernel->bootstrap();

$listings = \\App\\Models\\MarketplaceListing::withTrashed()->where('title', 'like', '%barbecue%')->get();
$count = 0;
foreach ($listings as $listing) {
    $listing->restore();
    $count++;
}
echo "Restored " . $count . " listings.";
"""

sftp = client.open_sftp()
with open("restore.php", "w", encoding="utf-8") as f:
    f.write(php_script)
sftp.put('restore.php', '/tmp/restore.php')
sftp.close()

client.exec_command(f"kubectl cp /tmp/restore.php {pod}:/app/restore.php")
stdin, stdout, stderr = client.exec_command(f"kubectl exec {pod} -- php /app/restore.php")

print("=== Output ===")
print(stdout.read().decode())
print(stderr.read().decode())

client.close()
