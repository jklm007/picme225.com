import paramiko

def run_ssh_command(host, user, password, command):
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    try:
        client.connect(host, username=user, password=password, timeout=10)
        stdin, stdout, stderr = client.exec_command(command)
        out = stdout.read().decode(errors='replace')
        err = stderr.read().decode(errors='replace')
        return out, err
    except Exception as e:
        return str(e), ""
    finally:
        client.close()

if __name__ == "__main__":
    commands = [
        "k3s kubectl logs deployment/laravel-deployment --tail=100",
        "k3s kubectl exec deployment/laravel-deployment -- tail -n 100 /app/storage/logs/laravel.log 2>/dev/null || echo 'No laravel.log'"
    ]
    for cmd in commands:
        out, err = run_ssh_command('109.199.123.69', 'root', 'Charlotte23', cmd)
        print(f"=== {cmd} ===")
        print("OUT:\n", out)
        if err: print("ERR:\n", err)
