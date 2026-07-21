import paramiko

hostname = '109.199.123.69'
client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(hostname, username='root', password='Charlotte23')

cmd = """
POD=$(kubectl get pods -l app=laravel --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers | head -n 1)
kubectl exec $POD -- sh -c 'PGPASSWORD=secret_password psql -h postgres-service -U picme_user -d picme_db -c "SELECT id, cover_image FROM marketplace_listings ORDER BY id DESC LIMIT 15;"'
"""

stdin, stdout, stderr = client.exec_command(cmd)
print('Out:', stdout.read().decode())
print('Err:', stderr.read().decode())
client.close()
