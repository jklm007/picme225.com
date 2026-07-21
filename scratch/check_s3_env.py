import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

stdin, stdout, stderr = client.exec_command("kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}'")
pod = stdout.read().decode().strip().strip("'")

# Dump AWS and R2 variables from the pod
cmd = f"kubectl exec {pod} -- env | grep -E 'AWS_|R2_|FILESYSTEM_DISK'"
stdin, stdout, stderr = client.exec_command(cmd)
print("=== Output ===")
print(stdout.read().decode())
print(stderr.read().decode())

client.close()
