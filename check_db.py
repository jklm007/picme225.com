import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

stdin, stdout, stderr = client.exec_command("kubectl get pod -l app=laravel -o jsonpath='{.items[0].metadata.name}'")
pod = stdout.read().decode().strip()

command = f"kubectl exec {pod} -- bash -c \"PGPASSWORD=secret_password psql -h postgres-service -U picme_user -d picme_db -c \\\"SELECT key, value FROM settings WHERE key LIKE 'r2%';\\\"\""
stdin, stdout, stderr = client.exec_command(command)
print(stdout.read().decode())
print(stderr.read().decode())
