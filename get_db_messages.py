import paramiko, json

hostname = '109.199.123.69'
username = 'root'
password = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(hostname, username=username, password=password)

# Query database for recent messages sent to Prince and the wife to see status
cmd = """kubectl exec deploy/laravel-deployment -- php -r "
require 'vendor/autoload.php';
\$app = require_once 'bootstrap/app.php';
\$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
\$msgs = App\Models\WhatsappMessage::orderBy('id', 'desc')->take(20)->get(['id', 'group_id', 'whatsapp_user_id', 'message_id', 'created_at']);
echo json_encode(\$msgs->toArray());
"
"""
stdin, stdout, stderr = client.exec_command(cmd)
out = stdout.read().decode('utf-8', errors='replace')
err = stderr.read().decode('utf-8', errors='replace')
print("DB MESSAGES:", out)
if err: print("ERR:", err)

client.close()
