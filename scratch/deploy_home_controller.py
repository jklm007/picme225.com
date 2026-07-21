import paramiko
import os

HOSTNAME = '109.199.123.69'
USERNAME = 'root'
PASSWORD = 'Charlotte23'

FILES = [
    ('app/Http/Controllers/HomeController.php', '/app/app/Http/Controllers/HomeController.php'),
    ('resources/views/user/dashboard.blade.php', '/app/resources/views/user/dashboard.blade.php'),
    ('resources/views/user/include/header.blade.php', '/app/resources/views/user/include/header.blade.php'),
    ('resources/views/user/include/nav.blade.php', '/app/resources/views/user/include/nav.blade.php'),
    ('resources/views/common/pwa_installer.blade.php', '/app/resources/views/common/pwa_installer.blade.php'),
    ('resources/views/user/layout/base.blade.php', '/app/resources/views/user/layout/base.blade.php'),
]

def run_ssh(client, cmd, desc=""):
    print(f"  >>> {desc or cmd}")
    stdin, stdout, stderr = client.exec_command(cmd)
    out = stdout.read().decode('utf-8', errors='replace').strip()
    err = stderr.read().decode('utf-8', errors='replace').strip()
    return out, err

def main():
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(HOSTNAME, username=USERNAME, password=PASSWORD, timeout=30)
    print("Connected!")

    pods_out, _ = run_ssh(client, "kubectl get pods -l app=laravel --no-headers -o custom-columns=NAME:.metadata.name")
    pods = [p.strip() for p in pods_out.splitlines() if p.strip() and 'NAME' not in p]
    
    sftp = client.open_sftp()
    for local_path, remote_pod_path in FILES:
        remote_tmp = f"/tmp/deploy_{os.path.basename(local_path)}"
        sftp.put(local_path, remote_tmp)
        for pod in pods:
            run_ssh(client, f"kubectl cp {remote_tmp} default/{pod}:{remote_pod_path}", f"copy {os.path.basename(local_path)} to {pod}")

    sftp.close()

    for pod in pods:
        run_ssh(client, f"kubectl exec {pod} -- php artisan optimize:clear", f"optimize:clear on {pod}")

    client.close()
    print("\n=== DONE ===")

if __name__ == '__main__':
    main()
