import paramiko

hostname = '109.199.123.69'
username = 'root'
password = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(hostname, username=username, password=password)

stdin, stdout, stderr = client.exec_command("kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}'")
pod_name = stdout.read().decode('utf-8').strip()

# Check routes/provider.php
stdin, stdout, stderr = client.exec_command(f"kubectl exec {pod_name} -- cat routes/provider.php")
print("PROD provider.php:")
print(stdout.read().decode('utf-8')[:500])

client.close()
