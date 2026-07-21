import paramiko

hostname = '109.199.123.69'
client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(hostname, username='root', password='Charlotte23')

cmd = """
LARAVEL_PODS=$(kubectl get pods -l app=laravel --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers)
for POD in $LARAVEL_PODS; do
    echo "Fixing symlink on $POD..."
    kubectl exec $POD -- rm -rf public/storage
    kubectl exec $POD -- php artisan storage:link
done
"""

stdin, stdout, stderr = client.exec_command(cmd)
print('Out:', stdout.read().decode())
print('Err:', stderr.read().decode())
client.close()
