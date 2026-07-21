import paramiko
import sys

hostname = '109.199.123.69'
username = 'root'
password = 'Charlotte23'

commands = """
POD_WORKER=$(kubectl get pods -l app=laravel-worker --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers | head -n 1)
POD_WEB=$(kubectl get pods -l app=laravel --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers | head -n 1)

echo "=== WORKER LOGS (tail 50) ==="
kubectl exec $POD_WORKER -- tail -n 50 /app/storage/logs/laravel.log

echo "=== WEB LOGS (tail 50) ==="
kubectl exec $POD_WEB -- tail -n 50 /app/storage/logs/laravel.log
"""

try:
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(hostname, port=22, username=username, password=password, timeout=10)
    
    stdin, stdout, stderr = client.exec_command(commands)
    print(stdout.read().decode())
    err = stderr.read().decode()
    if err:
        print("ERRORS:", err)
    
    client.close()
except Exception as e:
    print(f"Error: {e}")
