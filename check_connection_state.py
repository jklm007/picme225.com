import paramiko

hostname = '109.199.123.69'
username = 'root'
password = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(hostname, username=username, password=password)

# Query Evolution API instance connection status
cmd = """kubectl exec deploy/laravel-deployment -- curl -s -X GET http://evolution-api-service:8080/instance/connectionState/picme_whatsapp -H "apikey: picme225-evolution-secret-key" """
stdin, stdout, stderr = client.exec_command(cmd)
out = stdout.read().decode('utf-8', errors='replace')

client.close()

print("CONNECTION STATE:", out)
