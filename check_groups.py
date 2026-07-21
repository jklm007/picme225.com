import paramiko, json

hostname = '109.199.123.69'
username = 'root'
password = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(hostname, username=username, password=password)

# Query whatsapp_groups and get whatsapp_messages groups
cmd = """kubectl exec deploy/laravel-deployment -- php -r "
require 'vendor/autoload.php';
\$app = require_once 'bootstrap/app.php';
\$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
\$groups = App\Models\WhatsappGroup::all(['id', 'group_id', 'name']);
echo json_encode(\$groups->toArray());
"
"""
stdin, stdout, stderr = client.exec_command(cmd)
out = stdout.read().decode('utf-8', errors='replace')
err = stderr.read().decode('utf-8', errors='replace')
print("GROUPS:", out)
if err: print("ERR:", err[:300])

# Also get a sample listing to see the owner_phone vs whatsapp user
cmd2 = """kubectl exec deploy/laravel-deployment -- php -r "
require 'vendor/autoload.php';
\$app = require_once 'bootstrap/app.php';
\$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
\$listing = App\Models\MarketplaceListing::with('whatsappMessage.whatsappUser')->orderBy('id','desc')->first();
echo json_encode(['id'=>\$listing->id,'owner_phone'=>\$listing->owner_phone,'msg_group'=>\$listing->whatsappMessage ? \$listing->whatsappMessage->group_id : null,'wa_user'=>\$listing->whatsappMessage && \$listing->whatsappMessage->whatsappUser ? \$listing->whatsappMessage->whatsappUser->whatsapp_id : null]);
"
"""
stdin2, stdout2, stderr2 = client.exec_command(cmd2)
out2 = stdout2.read().decode('utf-8', errors='replace')
err2 = stderr2.read().decode('utf-8', errors='replace')
print("LISTING:", out2)
if err2: print("ERR2:", err2[:300])

client.close()
