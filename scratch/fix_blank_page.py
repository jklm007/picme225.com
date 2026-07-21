import paramiko
import os

HOSTNAME = '109.199.123.69'
USERNAME = 'root'
PASSWORD = 'Charlotte23'

FILES = [
    (
        'app/Http/Controllers/Admin/ListingPhotoController.php',
        '/app/app/Http/Controllers/Admin/ListingPhotoController.php'
    ),
]

def run_ssh(client, cmd, desc=""):
    print(f"  >>> {desc or cmd}")
    stdin, stdout, stderr = client.exec_command(cmd)
    out = stdout.read().decode('utf-8', errors='replace').strip()
    err = stderr.read().decode('utf-8', errors='replace').strip()
    if out:
        print(f"  OUT: {out}")
    if err:
        print(f"  ERR: {err}")
    return out, err

def main():
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(HOSTNAME, username=USERNAME, password=PASSWORD, timeout=30)
    print("Connected!")

    # Get pod
    pods_out, _ = run_ssh(client, "kubectl get pods -l app=laravel --no-headers -o custom-columns=NAME:.metadata.name", "Get pods")
    pods = [p.strip() for p in pods_out.splitlines() if p.strip() and 'NAME' not in p]
    print(f"Pods: {pods}")

    sftp = client.open_sftp()

    for local_path, remote_pod_path in FILES:
        if not os.path.exists(local_path):
            print(f"SKIP (missing locally): {local_path}")
            continue

        remote_tmp = f"/tmp/deploy_{os.path.basename(local_path)}"
        print(f"\n--- Deploying {local_path} ---")
        sftp.put(local_path, remote_tmp)

        for pod in pods:
            pod_dir = os.path.dirname(remote_pod_path)
            run_ssh(client, f"kubectl exec {pod} -- mkdir -p {pod_dir}", f"mkdir on {pod}")
            run_ssh(client, f"kubectl cp {remote_tmp} default/{pod}:{remote_pod_path}", f"copy to {pod}")

    sftp.close()

    # Clear caches and optimize
    print("\n=== Clearing caches ===")
    for pod in pods:
        run_ssh(client, f"kubectl exec {pod} -- php artisan route:clear", f"route:clear on {pod}")
        run_ssh(client, f"kubectl exec {pod} -- php artisan view:clear", f"view:clear on {pod}")
        run_ssh(client, f"kubectl exec {pod} -- php artisan config:clear", f"config:clear on {pod}")
        run_ssh(client, f"kubectl exec {pod} -- php artisan optimize", f"optimize on {pod}")

    # Verify homepage now returns content
    print("\n=== Verifying homepage ===")
    pod = pods[0]
    out, _ = run_ssh(client, f"kubectl exec {pod} -- curl -s -o /dev/null -w '%{{http_code}}' -H 'Host: picme225.site' http://127.0.0.1/", "HTTP status code")
    print(f"Homepage HTTP status: {out}")

    client.close()
    print("\n=== DONE ===")

if __name__ == '__main__':
    main()
