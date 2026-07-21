import paramiko

hostname = '109.199.123.69'
client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(hostname, username='root', password='Charlotte23')

cmd = """
POD=$(kubectl get pods -l app=laravel --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers | head -n 1)
echo "Checking symlink on $POD..."
kubectl exec $POD -- ls -l public/storage
kubectl exec $POD -- php artisan storage:link
"""

stdin, stdout, stderr = client.exec_command(cmd)
print("Out:", stdout.read().decode())
print("Err:", stderr.read().decode())
client.close()
