import paramiko
import sys

hostname = '109.199.123.69'
client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(hostname, username='root', password='Charlotte23')

files_to_upload = {
    r'resources\views\admin\ad-campaign\create.blade.php': '/tmp/picme225/resources/views/admin/ad-campaign/create.blade.php',
    r'resources\views\admin\ad-campaign\edit.blade.php': '/tmp/picme225/resources/views/admin/ad-campaign/edit.blade.php',
    r'resources\views\admin\ad-campaign\index.blade.php': '/tmp/picme225/resources/views/admin/ad-campaign/index.blade.php',
    r'app\Http\Controllers\Resource\AdCampaignResource.php': '/tmp/picme225/app/Http/Controllers/Resource/AdCampaignResource.php',
    r'app\Http\Controllers\PrivateAdApiController.php': '/tmp/picme225/app/Http/Controllers/PrivateAdApiController.php',
    r'resources\views\user\layout\app.blade.php': '/tmp/picme225/resources/views/user/layout/app.blade.php',
    r'resources\views\user\layout\base.blade.php': '/tmp/picme225/resources/views/user/layout/base.blade.php',
}

sftp = client.open_sftp()
for local_path, remote_path in files_to_upload.items():
    with open(local_path, 'r', encoding='utf-8') as f:
        content = f.read()
    with sftp.open(remote_path, 'w') as f:
        f.write(content)
    print(f"Uploaded {local_path} to {remote_path}")
sftp.close()

cmd = "cd /tmp/picme225 && bash rebuild_laravel.sh"
print("Running rebuild_laravel.sh on the server to bake changes into the Docker image...")
stdin, stdout, stderr = client.exec_command(cmd)

# Read output as it comes
for line in iter(stdout.readline, ""):
    print(line, end="")
for line in iter(stderr.readline, ""):
    print(line, end="", file=sys.stderr)

client.close()
