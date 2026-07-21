import paramiko
import sys

hostname = '109.199.123.69'
client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(hostname, username='root', password='Charlotte23')

files_to_upload = {
    r'resources\views\admin\ad-campaign\create.blade.php': '/tmp/create.blade.php',
    r'resources\views\admin\ad-campaign\edit.blade.php': '/tmp/edit.blade.php',
    r'resources\views\admin\ad-campaign\index.blade.php': '/tmp/index.blade.php',
    r'app\Http\Controllers\Resource\AdCampaignResource.php': '/tmp/AdCampaignResource.php',
    r'app\Http\Controllers\PrivateAdApiController.php': '/tmp/PrivateAdApiController.php',
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
    kubectl cp /tmp/create.blade.php default/$POD:/app/resources/views/admin/ad-campaign/create.blade.php
    kubectl cp /tmp/edit.blade.php default/$POD:/app/resources/views/admin/ad-campaign/edit.blade.php
    kubectl cp /tmp/index.blade.php default/$POD:/app/resources/views/admin/ad-campaign/index.blade.php
    kubectl cp /tmp/AdCampaignResource.php default/$POD:/app/app/Http/Controllers/Resource/AdCampaignResource.php
    kubectl cp /tmp/PrivateAdApiController.php default/$POD:/app/app/Http/Controllers/PrivateAdApiController.php
    kubectl exec $POD -- php artisan view:clear
    echo "Deployed to $POD"
done
"""
print("Running kubectl cp on pods...")
stdin, stdout, stderr = client.exec_command(cmd)
print('Out:', stdout.read().decode())
print('Err:', stderr.read().decode())
client.close()
