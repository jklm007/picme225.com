import paramiko
import sys

hostname = '109.199.123.69'
username = 'root'
password = 'Charlotte23'

commands = """
POD_WORKER=$(kubectl get pods -l app=laravel-worker --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers | head -n 1)
POD_WEB=$(kubectl get pods -l app=laravel --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers | head -n 1)

echo "=== WORKER CONTAINER LOGS (tail 150) ==="
kubectl logs $POD_WORKER --tail=150

echo "=== WEB CONTAINER LOGS (tail 150) ==="
kubectl logs $POD_WEB --tail=150
"""

try:
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(hostname, port=22, username=username, password=password, timeout=10)
    
    stdin, stdout, stderr = client.exec_command(commands)
    output = stdout.read().decode('utf-8', errors='replace')
    
    with open('logs.txt', 'w', encoding='utf-8') as f:
        f.write(output)
        
    err = stderr.read().decode('utf-8', errors='replace')
    if err:
        print("ERRORS:")
        print(err)
        
    client.close()
    print("Logs written to logs.txt")
except Exception as e:
    print(f"Error: {e}")
