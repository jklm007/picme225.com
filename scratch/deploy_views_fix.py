import paramiko
import os

HOSTNAME = '109.199.123.69'
USERNAME = 'root'
PASSWORD = 'Charlotte23'

FILES = [
    ('resources/views/home.blade.php',                '/app/resources/views/home.blade.php'),
    ('resources/views/user/layout/app.blade.php',     '/app/resources/views/user/layout/app.blade.php'),
    ('resources/views/user/layout/base.blade.php',    '/app/resources/views/user/layout/base.blade.php'),
    ('resources/views/user/dashboard.blade.php',      '/app/resources/views/user/dashboard.blade.php'),
    ('resources/views/drive.blade.php',               '/app/resources/views/drive.blade.php'),
    ('resources/views/marketplace/detail.blade.php',  '/app/resources/views/marketplace/detail.blade.php'),
]

def run_ssh(client, cmd, desc=""):
    print(f"  >>> {desc or cmd}")
    stdin, stdout, stderr = client.exec_command(cmd)
    out = stdout.read().decode('utf-8', errors='replace').strip()
    err = stderr.read().decode('utf-8', errors='replace').strip()
    if out: print(f"  OUT: {out}")
    if err: print(f"  ERR: {err}")
    return out, err

def main():
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(HOSTNAME, username=USERNAME, password=PASSWORD, timeout=30)
    print("Connected!")

    pods_out, _ = run_ssh(client, "kubectl get pods -l app=laravel --no-headers -o custom-columns=NAME:.metadata.name")
    pods = [p.strip() for p in pods_out.splitlines() if p.strip() and 'NAME' not in p]
    print(f"Pods: {pods}")

    sftp = client.open_sftp()

    for local_path, remote_pod_path in FILES:
        if not os.path.exists(local_path):
            print(f"  SKIP (not found locally): {local_path}")
            continue
        local_size = os.path.getsize(local_path)
        remote_tmp = f"/tmp/deploy_{os.path.basename(local_path)}"
        print(f"\n--- Deploying {local_path} ({local_size} bytes) ---")
        sftp.put(local_path, remote_tmp)
        for pod in pods:
            pod_dir = os.path.dirname(remote_pod_path)
            run_ssh(client, f"kubectl exec {pod} -- mkdir -p {pod_dir}", f"mkdir {pod}")
            run_ssh(client, f"kubectl cp {remote_tmp} default/{pod}:{remote_pod_path}", f"copy to {pod}")
            run_ssh(client, f"kubectl exec {pod} -- wc -c {remote_pod_path}", f"verify size on {pod}")

    sftp.close()

    print("\n=== Clearing caches ===")
    for pod in pods:
        run_ssh(client, f"kubectl exec {pod} -- php artisan optimize:clear", f"optimize:clear on {pod}")
        run_ssh(client, f"kubectl exec {pod} -- php artisan view:clear", f"view:clear on {pod}")

    client.close()
    print("\n=== DONE ===")

if __name__ == '__main__':
    main()
