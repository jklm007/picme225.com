import paramiko

hostname = '109.199.123.69'
username = 'root'
password = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(hostname, username=username, password=password)

# Check all possible 10-digit formats (with prefixes 05 for MTN and 01 for Moov)
cmd = """kubectl exec deploy/laravel-deployment -- curl -s -X POST http://evolution-api-service:8080/chat/whatsappNumbers/picme_whatsapp -H "apikey: picme225-evolution-secret-key" -H "Content-Type: application/json" -d '{"numbers":["2250558286571", "2250158286571"]}' """
stdin, stdout, stderr = client.exec_command(cmd)
out = stdout.read().decode('utf-8', errors='replace')

client.close()

print("CHECK NUMBERS:", out)
