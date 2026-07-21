import paramiko, json

hostname = '109.199.123.69'
username = 'root'
password = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(hostname, username=username, password=password)

# Query the database for the user with phone 22558286571 or similar
cmd = """kubectl exec deploy/laravel-deployment -- php -r "
require 'vendor/autoload.php';
\$app = require_once 'bootstrap/app.php';
\$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
\$user = App\Models\WhatsappUser::where('phone_number', 'like', '%58286571%')->first();
echo json_encode(\$user ? \$user->toArray() : null);
"
"""
stdin, stdout, stderr = client.exec_command(cmd)
out = stdout.read().decode('utf-8', errors='replace')
err = stderr.read().decode('utf-8', errors='replace')
print("DB USER:", out)
if err: print("ERR:", err)

client.close()
