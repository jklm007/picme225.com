import paramiko
import time

for attempt in range(3):
    try:
        client = paramiko.SSHClient()
        client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
        client.connect('109.199.123.69', username='root', password='Charlotte23', timeout=15)
        break
    except Exception as e:
        print(f"Attempt {attempt+1} failed: {e}")
        time.sleep(5)

# Get the pod name (laravel-app)
stdin, stdout, stderr = client.exec_command("kubectl get pods | grep laravel")
pods_output = stdout.read().decode()
print("Pods:\n", pods_output)

client.close()
