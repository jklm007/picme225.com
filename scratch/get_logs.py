import paramiko

HOSTNAME = '109.199.123.69'
USERNAME = 'root'
PASSWORD = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOSTNAME, username=USERNAME, password=PASSWORD, timeout=10)

stdin, stdout, stderr = client.exec_command("kubectl get pods -l app=laravel --no-headers -o custom-columns=NAME:.metadata.name | head -n 1")
pod = stdout.read().decode().strip()

if pod:
    stdin, stdout, stderr = client.exec_command(f"kubectl logs {pod} --tail=100")
    with open('scratch/pod_logs.txt', 'wb') as f:
        f.write(stdout.read())
        f.write(stderr.read())
    print("Logs saved to scratch/pod_logs.txt")
else:
    print("Pod not found")
