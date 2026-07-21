import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

print("=== WORKER LOGS ===")
stdin, stdout, stderr = client.exec_command("kubectl get pods -l app=laravel-worker -o jsonpath='{.items[0].metadata.name}'")
worker_pod = stdout.read().decode().strip().replace("'", "")
if worker_pod:
    stdin, stdout, stderr = client.exec_command(f"kubectl logs --tail=100 {worker_pod}")
    print(stdout.read().decode())
    print(stderr.read().decode())

print("\n=== WEB LOGS ===")
stdin, stdout, stderr = client.exec_command("kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}'")
web_pod = stdout.read().decode().strip().replace("'", "")
if web_pod:
    stdin, stdout, stderr = client.exec_command(f"kubectl logs --tail=100 {web_pod}")
    print(stdout.read().decode())
    print(stderr.read().decode())

client.close()
