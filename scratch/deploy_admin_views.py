import paramiko
import os
import time

SERVER_IP = "109.199.123.69"
SERVER_USER = "root"
SERVER_PASS = "Charlotte23"

# Les fichiers locaux à uploader
FILES_TO_DEPLOY = {
    "resources/views/admin/marketplace/listings/_listing_row.blade.php": "/var/www/picme/resources/views/admin/marketplace/listings/_listing_row.blade.php",
    "resources/views/admin/whatsapp/index.blade.php": "/var/www/picme/resources/views/admin/whatsapp/index.blade.php"
}

POD_PREFIX = "laravel-deployment-"
NAMESPACE = "default"

def deploy():
    print("Connecting to server...")
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(SERVER_IP, username=SERVER_USER, password=SERVER_PASS)
    sftp = ssh.open_sftp()
    
    # 1. Upload files to a temporary location on the server
    print("Uploading files to server /tmp...")
    for local_path, remote_path in FILES_TO_DEPLOY.items():
        base_name = os.path.basename(local_path)
        tmp_path = f"/tmp/{base_name}"
        print(f" -> {local_path} to {tmp_path}")
        sftp.put(local_path, tmp_path)
    
    # 2. Find the pod
    print("Finding the Laravel pod...")
    stdin, stdout, stderr = ssh.exec_command(f"kubectl get pods -n {NAMESPACE} | grep {POD_PREFIX} | grep Running | awk '{{print $1}}'")
    pods = stdout.read().decode('utf-8').strip().split('\n')
    
    if not pods or not pods[0]:
        print("No running Laravel pod found!")
        return
        
    for pod in pods:
        if not pod: continue
        print(f"Updating Pod: {pod}")
        
        for local_path, pod_path in FILES_TO_DEPLOY.items():
            base_name = os.path.basename(local_path)
            tmp_path = f"/tmp/{base_name}"
            print(f" -> Copying into pod: {pod_path}")
            ssh.exec_command(f"kubectl cp {tmp_path} {NAMESPACE}/{pod}:{pod_path}")
            
    # 3. Clear cache in the pod
    pod = pods[0]
    print(f"Clearing cache in {pod}...")
    ssh.exec_command(f"kubectl exec {pod} -n {NAMESPACE} -- php artisan view:clear")
    
    print("Deployment completed!")
    sftp.close()
    ssh.close()

if __name__ == "__main__":
    deploy()
