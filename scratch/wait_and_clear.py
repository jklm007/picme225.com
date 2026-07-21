import paramiko
import time
import sys

HOSTNAME = '109.199.123.69'
USERNAME = 'root'
PASSWORD = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOSTNAME, username=USERNAME, password=PASSWORD, timeout=30)

def run(cmd):
    stdin, stdout, stderr = client.exec_command(cmd)
    return stdout.read().decode('utf-8', errors='replace').strip()

print("Waiting for new laravel pod to be READY...")
new_pod = None
for i in range(30):
    pods_info = run("kubectl get pods -l app=laravel --no-headers")
    # Looking for a pod with 1/1 READY
    for line in pods_info.split('\n'):
        if '1/1' in line and 'Running' in line:
            new_pod = line.split()[0]
            # Make sure it's not the old terminating one
            if 'Terminating' not in line:
                break
    if new_pod:
        break
    time.sleep(2)

if not new_pod:
    print("Pod not ready yet.")
    sys.exit(1)

print(f"New pod is ready: {new_pod}")
print("Clearing caches...")
run(f"kubectl exec {new_pod} -- php artisan optimize:clear")
run(f"kubectl exec {new_pod} -- php artisan view:clear")
run(f"kubectl exec {new_pod} -- php artisan config:clear")
print("Done clearing caches.")

client.close()
