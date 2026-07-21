import paramiko

hostname = '109.199.123.69'
username = 'root'
password = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(hostname, username=username, password=password)

# Check if the 10-digit format of the admin number exists
cmd = """kubectl exec deploy/laravel-deployment -- curl -s -X POST http://evolution-api-service:8080/chat/whatsappNumbers/picme_whatsapp -H "apikey: picme225-evolution-secret-key" -H "Content-Type: application/json" -d '{"numbers":["2250759747444"]}' """
stdin, stdout, stderr = client.exec_command(cmd)
out = stdout.read().decode('utf-8', errors='replace')

client.close()

print("CHECK ADMIN 10-DIGIT:", out)
