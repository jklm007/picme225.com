import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

cmd = "kubectl exec $(kubectl get pods -l app=laravel-worker -o jsonpath='{.items[0].metadata.name}') -- grep -r -i -B 2 -A 5 'Groq' storage/logs/ | tail -n 100"
stdin, stdout, stderr = client.exec_command(cmd)
print(stdout.read().decode())
client.close()
