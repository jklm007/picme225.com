import paramiko
import sys

hostname = '109.199.123.69'
client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(hostname, username='root', password='Charlotte23')

local_path = r'resources\views\admin\marketplace\listings\_listing_row.blade.php'
remote_tmp_path = '/tmp/_listing_row.blade.php'

sftp = client.open_sftp()
with open(local_path, 'r', encoding='utf-8') as f:
    content = f.read()
with sftp.open(remote_tmp_path, 'w') as f:
    f.write(content)
sftp.close()

cmd = """
LARAVEL_PODS=$(kubectl get pods -l app=laravel --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers)
for POD in $LARAVEL_PODS; do
    kubectl cp /tmp/_listing_row.blade.php default/$POD:/app/resources/views/admin/marketplace/listings/_listing_row.blade.php
    kubectl exec $POD -- php artisan view:clear
    echo "Deployed _listing_row.blade.php to $POD"
done
"""
print("Deploying...")
stdin, stdout, stderr = client.exec_command(cmd)
print('Out:', stdout.read().decode())
print('Err:', stderr.read().decode())
client.close()
