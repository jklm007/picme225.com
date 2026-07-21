import paramiko
import sys

hostname = '109.199.123.69'
client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(hostname, username='root', password='Charlotte23')

files_to_upload = {
    r'routes\admin.php': '/tmp/admin.php',
    r'app\Http\Controllers\Admin\MarketplaceListingController.php': '/tmp/MarketplaceListingController.php',
    r'app\Http\Controllers\Admin\WhatsappListingController.php': '/tmp/WhatsappListingController.php',
    r'resources\views\admin\marketplace\listings\index.blade.php': '/tmp/marketplace_index.blade.php',
    r'resources\views\admin\marketplace\listings\_listing_row.blade.php': '/tmp/_listing_row.blade.php',
    r'resources\views\admin\whatsapp\index.blade.php': '/tmp/whatsapp_index.blade.php',
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
    kubectl cp /tmp/admin.php default/$POD:/app/routes/admin.php
    kubectl cp /tmp/MarketplaceListingController.php default/$POD:/app/app/Http/Controllers/Admin/MarketplaceListingController.php
    kubectl cp /tmp/WhatsappListingController.php default/$POD:/app/app/Http/Controllers/Admin/WhatsappListingController.php
    kubectl cp /tmp/marketplace_index.blade.php default/$POD:/app/resources/views/admin/marketplace/listings/index.blade.php
    kubectl cp /tmp/_listing_row.blade.php default/$POD:/app/resources/views/admin/marketplace/listings/_listing_row.blade.php
    kubectl cp /tmp/whatsapp_index.blade.php default/$POD:/app/resources/views/admin/whatsapp/index.blade.php
    kubectl exec $POD -- php artisan view:clear
    kubectl exec $POD -- php artisan route:clear
    echo "Deployed to $POD"
done
"""
print("Running kubectl cp on pods...")
stdin, stdout, stderr = client.exec_command(cmd)
print('Out:', stdout.read().decode())
print('Err:', stderr.read().decode())
client.close()
