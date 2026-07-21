import paramiko, json

hostname = '109.199.123.69'
username = 'root'
password = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(hostname, username=username, password=password)

# Run curl from inside the deployment pod using kubectl exec
cmd = """kubectl exec deploy/laravel-deployment -- curl -X POST http://evolution-api-service:8080/message/sendText/picme_whatsapp -H "apikey: picme225-evolution-secret-key" -H "Content-Type: application/json" -d '{"number":"22558286571@s.whatsapp.net","options":{"delay":1200,"presence":"composing"},"text":"Test de connexion bot vers client."}'"""
stdin, stdout, stderr = client.exec_command(cmd)
out = stdout.read().decode('utf-8', errors='replace')
err = stderr.read().decode('utf-8', errors='replace')
print("STDOUT:", out)
print("STDERR:", err)

client.close()
