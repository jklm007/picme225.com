import paramiko

def run_ssh_command(host, user, password, command):
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    try:
        client.connect(host, username=user, password=password, timeout=10)
        stdin, stdout, stderr = client.exec_command(command)
        out = stdout.read().decode()
        err = stderr.read().decode()
        return out, err
    except Exception as e:
        return str(e), ""
    finally:
        client.close()

if __name__ == "__main__":
    commands = [
        "k3s kubectl exec deploy/laravel-deployment -- ls -la /app/resources/views/user/marketplace/ 2>/dev/null || echo 'Not found'",
        "k3s kubectl exec deploy/laravel-deployment -- ls -la /app/resources/views/admin/ad-campaigns/ 2>/dev/null || echo 'Not found'",
        "k3s kubectl exec deploy/laravel-deployment -- cat /app/resources/views/home.blade.php | wc -l"
    ]
    for cmd in commands:
        print(f"=== {cmd} ===")
        out, err = run_ssh_command('109.199.123.69', 'root', 'Charlotte23', cmd)
        print("OUT:", out)
        if err: print("ERR:", err)
