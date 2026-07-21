import paramiko
import base64
import time

SERVER_IP = "109.199.123.69"
SERVER_USER = "root"
SERVER_PASS = "Charlotte23"
NAMESPACE = "default"

FILES_TO_DEPLOY = {
    "app/Helper/ViewHelper.php": "/var/www/picme/app/Helper/ViewHelper.php",
    "resources/views/admin/marketplace/listings/_listing_row.blade.php": "/var/www/picme/resources/views/admin/marketplace/listings/_listing_row.blade.php",
    "resources/views/admin/marketplace/listings/photos.blade.php": "/var/www/picme/resources/views/admin/marketplace/listings/photos.blade.php",
    "resources/views/admin/whatsapp/index.blade.php": "/var/www/picme/resources/views/admin/whatsapp/index.blade.php",
}

def deploy():
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(SERVER_IP, username=SERVER_USER, password=SERVER_PASS)
    sftp = ssh.open_sftp()

    print("Uploading files to server /tmp...")
    for local_path, pod_path in FILES_TO_DEPLOY.items():
        base_name = local_path.replace("/", "_").replace("\\", "_")
        tmp_path = f"/tmp/deploy_{base_name}"
        sftp.put(local_path, tmp_path)
        print(f"  -> {local_path}")

    stdin, stdout, stderr = ssh.exec_command(f"kubectl get pods -n {NAMESPACE} | grep laravel-deployment | grep Running | awk '{{print $1}}'")
    pods = stdout.read().decode().strip().split('\n')
    if not pods or not pods[0]:
        print("No running pod found!")
        return

    for pod in pods:
        if not pod: continue
        print(f"\nDeploying to pod: {pod}")
        for local_path, pod_path in FILES_TO_DEPLOY.items():
            base_name = local_path.replace("/", "_").replace("\\", "_")
            tmp_path = f"/tmp/deploy_{base_name}"
            cmd = f"kubectl cp {tmp_path} {NAMESPACE}/{pod}:{pod_path}"
            stdin, stdout, stderr = ssh.exec_command(cmd)
            stdout.channel.recv_exit_status()
            print(f"  -> {pod_path}")

    pod = pods[0]
    print(f"\nClearing caches on {pod}...")
    ssh.exec_command(f"kubectl exec {pod} -n {NAMESPACE} -- php artisan view:clear")
    ssh.exec_command(f"kubectl exec {pod} -n {NAMESPACE} -- php artisan cache:clear")
    time.sleep(1)
    print("Done!")
    sftp.close()
    ssh.close()

if __name__ == "__main__":
    deploy()
