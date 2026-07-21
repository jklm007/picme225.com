import paramiko
import sys

hostname = '109.199.123.69'
client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(hostname, username='root', password='Charlotte23')

files_to_upload = {
    r'resources\views\user\layout\app.blade.php': '/tmp/app.blade.php',
    r'resources\views\user\layout\base.blade.php': '/tmp/base.blade.php',
}

sftp = client.open_sftp()
for local_path, remote_tmp_path in files_to_upload.items():
    with open(local_path, 'r', encoding='utf-8') as f:
        content = f.read()
    with sftp.open(remote_tmp_path, 'w') as f:
        f.write(content)
    print(f"Uploaded {local_path} to {remote_tmp_path}")
sftp.close()

cmd = """
LARAVEL_PODS=$(kubectl get pods -l app=laravel --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers)
for POD in $LARAVEL_PODS; do
    kubectl cp /tmp/app.blade.php default/$POD:/app/resources/views/user/layout/app.blade.php
    kubectl cp /tmp/base.blade.php default/$POD:/app/resources/views/user/layout/base.blade.php
    kubectl exec $POD -- php artisan view:clear
    echo "Deployed to $POD"
done
"""
print("Running kubectl cp on pods...")
stdin, stdout, stderr = client.exec_command(cmd)
print('Out:', stdout.read().decode())
print('Err:', stderr.read().decode())
client.close()
