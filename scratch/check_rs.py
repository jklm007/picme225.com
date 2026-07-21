import paramiko
import sys

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
        "k3s kubectl get rs laravel-deployment-ff9cc75cd -o yaml",
        "k3s kubectl describe rs laravel-deployment-ff9cc75cd"
    ]
    for cmd in commands:
        print(f"=== {cmd} ===")
        out, err = run_ssh_command('109.199.123.69', 'root', 'Charlotte23', cmd)
        if out: print(out)
        if err: print("ERROR:", err)
