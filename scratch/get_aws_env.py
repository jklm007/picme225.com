import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

pod = "laravel-deployment-56f54497f-r8pmg"
cmd = f"kubectl exec {pod} -- env | grep -E 'AWS_BUCKET|AWS_DEFAULT_REGION|AWS_URL'"
stdin, stdout, stderr = client.exec_command(cmd)
print("AWS ENV VARS:")
print(stdout.read().decode())
client.close()
