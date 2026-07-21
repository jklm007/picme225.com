import paramiko

hostname = '109.199.123.69'
username = 'root'
password = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(hostname, username=username, password=password)

stdin, stdout, stderr = client.exec_command("kubectl get deployment laravel-deployment -o yaml | grep -A 20 volumeMounts")
print(stdout.read().decode('utf-8'))

client.close()
