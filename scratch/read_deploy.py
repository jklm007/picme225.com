import paramiko
import sys

def run_ssh_command(host, user, password, command):
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    try:
        client.connect(host, username=user, password=password, timeout=10)
        stdin, stdout, stderr = client.exec_command(command)
        out = stdout.read().decode('utf-8', errors='replace')
        return out
    except Exception as e:
        return str(e)
    finally:
        client.close()

if __name__ == "__main__":
    out = run_ssh_command('109.199.123.69', 'root', 'Charlotte23', "cat /root/deploy_production.sh")
    with open("deploy_production.sh.txt", "w", encoding="utf-8") as f:
        f.write(out)
    print("Saved to deploy_production.sh.txt")
