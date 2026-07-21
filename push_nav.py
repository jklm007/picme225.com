import paramiko
import os

hostname = '109.199.123.69'
client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(hostname, username='root', password='Charlotte23')

# Read local file
with open(r'resources\views\admin\include\nav.blade.php', 'r', encoding='utf-8') as f:
    nav_content = f.read()

sftp = client.open_sftp()
with sftp.open('/tmp/nav.blade.php', 'w') as f:
    f.write(nav_content)
sftp.close()

cmd = """
LARAVEL_PODS=$(kubectl get pods -l app=laravel --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers)
for POD in $LARAVEL_PODS; do
    kubectl cp /tmp/nav.blade.php default/$POD:/app/resources/views/admin/include/nav.blade.php
    echo "Pushed nav.blade.php to $POD"
done
"""
stdin, stdout, stderr = client.exec_command(cmd)
print('Out:', stdout.read().decode())
print('Err:', stderr.read().decode())
client.close()
