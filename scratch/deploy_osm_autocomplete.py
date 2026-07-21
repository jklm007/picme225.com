import paramiko
import os
import sys

# --- Server config ---
HOSTNAME = '109.199.123.69'
USERNAME = 'root'
PASSWORD = 'Charlotte23'

# --- Files to deploy ---
# Format: (local_path, remote_path_in_pod)
FILES = [
    (
        'resources/views/home.blade.php',
        '/app/resources/views/home.blade.php'
    ),
    (
        'resources/views/admin/whatsapp/index.blade.php',
        '/app/resources/views/admin/whatsapp/index.blade.php'
    ),
    (
        'resources/views/admin/marketplace/listings/_listing_row.blade.php',
        '/app/resources/views/admin/marketplace/listings/_listing_row.blade.php'
    ),
    (
        'resources/views/admin/marketplace/listings/edit.blade.php',
        '/app/resources/views/admin/marketplace/listings/edit.blade.php'
    ),
    (
        'routes/admin.php',
        '/app/routes/admin.php'
    ),
    (
        'resources/views/admin/marketplace/listings/photos.blade.php',
        '/app/resources/views/admin/marketplace/listings/photos.blade.php'
    ),
    (
        'app/Http/Controllers/Admin/MarketplaceListingController.php',
        '/app/app/Http/Controllers/Admin/MarketplaceListingController.php'
    ),
]

def run_ssh(client, cmd, desc=""):
    print(f"  >>> {desc or cmd}")
    stdin, stdout, stderr = client.exec_command(cmd)
    out = stdout.read().decode('utf-8', errors='ignore').strip()
    err = stderr.read().decode('utf-8', errors='ignore').strip()
    if out:
        print(f"  OUT: {out}")
    if err:
        print(f"  ERR: {err}")
    return out, err

def main():
    print(f"=== Connexion au serveur {HOSTNAME} ===")
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(HOSTNAME, username=USERNAME, password=PASSWORD, timeout=30)
    print("Connecte !")

    # 1. Get pod names
    print("\n=== Recuperation des pods laravel ===")
    pods_out, _ = run_ssh(client, "kubectl get pods -l app=laravel --no-headers -o custom-columns=NAME:.metadata.name 2>/dev/null || k3s kubectl get pods -l app=laravel --no-headers -o custom-columns=NAME:.metadata.name", "Get pods")
    pods = [p.strip() for p in pods_out.splitlines() if p.strip() and 'NAME' not in p]
    
    if not pods:
        # Try broader search
        pods_out2, _ = run_ssh(client, "kubectl get pods --no-headers -o custom-columns=NAME:.metadata.name | grep laravel 2>/dev/null || k3s kubectl get pods --no-headers -o custom-columns=NAME:.metadata.name | grep laravel", "Get pods broader")
        pods = [p.strip() for p in pods_out2.splitlines() if p.strip()]
    
    if not pods:
        print("ERREUR: Aucun pod laravel trouve !")
        client.close()
        sys.exit(1)
    
    print(f"Pods trouves: {pods}")

    # 2. Upload files via sftp then kubectl cp
    sftp = client.open_sftp()
    
    for local_path, remote_pod_path in FILES:
        if not os.path.exists(local_path):
            print(f"  SKIP (non trouve localement): {local_path}")
            continue
        
        remote_tmp = f"/tmp/deploy_{os.path.basename(local_path)}"
        print(f"\n--- Deploiement de {local_path} ---")
        
        # Upload to server /tmp
        print(f"  Upload -> {remote_tmp}")
        sftp.put(local_path, remote_tmp)
        
        # Copy to each pod
        for pod in pods:
            # Make sure directory exists in pod
            pod_dir = os.path.dirname(remote_pod_path)
            run_ssh(client, f"kubectl exec {pod} -- mkdir -p {pod_dir} 2>/dev/null || k3s kubectl exec {pod} -- mkdir -p {pod_dir}", f"mkdir {pod}")
            
            cmd_cp = f"kubectl cp {remote_tmp} default/{pod}:{remote_pod_path} 2>/dev/null || k3s kubectl cp {remote_tmp} default/{pod}:{remote_pod_path}"
            run_ssh(client, cmd_cp, f"kubectl cp -> {pod}")
    
    sftp.close()

    # 3. Clear caches on each pod
    print("\n=== Nettoyage du cache ===")
    kubectl = "kubectl"
    for pod in pods:
        run_ssh(client, f"{kubectl} exec {pod} -- php artisan view:clear 2>/dev/null || k3s {kubectl} exec {pod} -- php artisan view:clear", f"view:clear on {pod}")
        run_ssh(client, f"{kubectl} exec {pod} -- php artisan config:clear 2>/dev/null || k3s {kubectl} exec {pod} -- php artisan config:clear", f"config:clear on {pod}")
        run_ssh(client, f"{kubectl} exec {pod} -- php artisan route:clear 2>/dev/null || k3s {kubectl} exec {pod} -- php artisan route:clear", f"route:clear on {pod}")
    
    client.close()
    print("\n=== DEPLOIEMENT TERMINE ! ===")

if __name__ == '__main__':
    main()
