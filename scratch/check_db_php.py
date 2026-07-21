import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

stdin, stdout, stderr = client.exec_command("kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}'")
pod = stdout.read().decode().strip().strip("'")

php_script = """
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$listings = \\App\\Models\\MarketplaceListing::select('id', 'cover_image', 'images')->orderBy('id', 'desc')->take(5)->get();
echo $listings->toJson();
"""

cmd = f"kubectl exec {pod} -- php -r \"{php_script}\""
stdin, stdout, stderr = client.exec_command(cmd)
print("=== Output ===")
print(stdout.read().decode())
err = stderr.read().decode()
if err:
    print("=== Error ===")
    print(err)

client.close()
