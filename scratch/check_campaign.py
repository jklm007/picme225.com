import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

# Check if the table exists and if storage link is active
stdin, stdout, stderr = client.exec_command(
    "kubectl exec $(kubectl get pods -l app=laravel-app -o jsonpath='{.items[0].metadata.name}') -- "
    "php artisan tinker --execute=\"echo Schema::hasTable('campaign_performances') ? 'TABLE_EXISTS' : 'TABLE_MISSING'; "
    "echo file_exists(public_path('storage')) ? '\\nLINK_EXISTS' : '\\nLINK_MISSING';\""
)
print(stdout.read().decode())
print(stderr.read().decode())
client.close()
