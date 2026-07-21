import os
import zipfile
import paramiko
import sys

def create_zip(zip_path):
    print("Creating zip archive (excluding heavy folders)...")
    exclude_roots = {'vendor', 'node_modules', 'storage', '.git', '.vscode', '.idea', 'scratch'}
    with zipfile.ZipFile(zip_path, 'w', zipfile.ZIP_DEFLATED) as zipf:
        for root, dirs, files in os.walk('.'):
            if root == '.':
                dirs[:] = [d for d in dirs if d not in exclude_roots]
            elif 'public/storage' in root.replace('\\', '/') or 'public/apk' in root.replace('\\', '/'):
                dirs[:] = []

            for file in files:
                if file.endswith('.zip') or file.endswith('.tar.gz') or file.endswith('.tar') or file.endswith('.apk'):
                    continue
                file_path = os.path.join(root, file)
                arcname = os.path.relpath(file_path, '.')
                try:
                    zipf.write(file_path, arcname)
                except FileNotFoundError:
                    pass
    print("Zip archive created.")

def main():
    zip_filename = 'picme_deploy.zip'
    create_zip(zip_filename)
    
    hostname = '109.199.123.69'
    username = 'root'
    password = 'Charlotte23'
    
    print(f"Connecting to {hostname}...")
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(hostname, username=username, password=password)
    
    print("Uploading zip file...")
    sftp = client.open_sftp()
    remote_zip = '/tmp/' + zip_filename
    sftp.put(zip_filename, remote_zip)
    sftp.close()
    
    print("Running build commands on server...")
    commands = """
    set -e
    rm -rf /tmp/picme225-build
    mkdir -p /tmp/picme225-build
    cd /tmp/picme225-build
    unzip -q /tmp/picme_deploy.zip || true
    
    echo "Building Docker image..."
    docker build -t picme225-laravel:latest -f Dockerfile.laravel .
    
    echo "Saving to tar and importing to k3s..."
    docker save picme225-laravel:latest > laravel.tar
    k3s ctr -n k8s.io images import laravel.tar
    
    echo "Restarting deployment..."
    kubectl rollout restart deployment/laravel-deployment
    kubectl rollout restart deployment/laravel-worker
    
    echo "Deployment successfully rolled out!"
    """
    
    stdin, stdout, stderr = client.exec_command(commands)
    
    # Print output in real-time
    print("Server output:")
    try:
        for line in iter(stdout.readline, ""):
            print(line.encode('ascii', 'ignore').decode('ascii'), end="")
    except Exception as e:
        print("Error reading stdout:", e)
        
    try:
        for line in iter(stderr.readline, ""):
            print("ERR: " + line.encode('ascii', 'ignore').decode('ascii'), end="")
    except Exception as e:
        pass
        
    client.close()
    
    if os.path.exists(zip_filename):
        os.remove(zip_filename)
        
    print("Done!")

if __name__ == '__main__':
    main()
