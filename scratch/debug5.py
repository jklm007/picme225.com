import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

stdin, stdout, stderr = client.exec_command("kubectl get pods -l app=laravel-worker -o jsonpath='{.items[0].metadata.name}'")
worker_pod = stdout.read().decode().strip().replace("'", "")
if worker_pod:
    stdin, stdout, stderr = client.exec_command(f"kubectl logs --tail=200 {worker_pod} | grep -A 20 -B 10 'Voiture'")
    print(stdout.read().decode())
    print(stderr.read().decode())

client.close()
