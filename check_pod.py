import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

stdin, stdout, stderr = client.exec_command("kubectl get pod -l app=laravel -o jsonpath='{.items[0].metadata.name}'")
pod = stdout.read().decode().strip().strip("'")

stdin, stdout, stderr = client.exec_command(f"kubectl exec {pod} -- grep -A 2 -B 2 'ACTIVE' app/Http/Controllers/HomeController.php")
print(stdout.read().decode())
