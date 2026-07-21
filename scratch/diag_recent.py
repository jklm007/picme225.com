import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

# Get worker pod
stdin, stdout, stderr = client.exec_command("kubectl get pods -l app=laravel-worker -o jsonpath='{.items[0].metadata.name}'")
worker_pod = stdout.read().decode().strip().strip("'")
print(f"Worker pod: {worker_pod}")

# Recent job logs
stdin, stdout, stderr = client.exec_command(f"kubectl logs --tail=300 {worker_pod} 2>&1 | grep -E 'WhatsApp Batch|ERROR|Exception|notif|envoi|Vision|Groq|modele|model|annonce|DONE|RUNNING' | tail -100")
logs = stdout.read().decode()
print("=== RECENT LOGS ===")
print(logs)

client.close()
