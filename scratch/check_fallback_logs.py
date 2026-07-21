import paramiko
import sys
import io

sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

stdin, stdout, stderr = client.exec_command("kubectl get pods -l app=laravel-worker -o jsonpath='{.items[0].metadata.name}'")
worker_pod = stdout.read().decode().strip().strip("'")

stdin, stdout, stderr = client.exec_command(f"kubectl logs --tail=500 {worker_pod} | grep -E 'Groq Fallback|Groq AutoDetect'")
print(stdout.read().decode('utf-8'))

client.close()
