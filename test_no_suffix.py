import paramiko

hostname = '109.199.123.69'
username = 'root'
password = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(hostname, username=username, password=password)

# Test 1: Send to 2250758286571 without JID suffix
cmd1 = """kubectl exec deploy/laravel-deployment -- curl -s -X POST http://evolution-api-service:8080/message/sendText/picme_whatsapp -H "apikey: picme225-evolution-secret-key" -H "Content-Type: application/json" -d '{"number":"2250758286571","text":"Test sans suffixe 10 chiffres"}'"""
stdin1, stdout1, stderr1 = client.exec_command(cmd1)
out1 = stdout1.read().decode('utf-8', errors='replace')

# Test 2: Send to 22558286571 without JID suffix
cmd2 = """kubectl exec deploy/laravel-deployment -- curl -s -X POST http://evolution-api-service:8080/message/sendText/picme_whatsapp -H "apikey: picme225-evolution-secret-key" -H "Content-Type: application/json" -d '{"number":"22558286571","text":"Test sans suffixe 8 chiffres"}'"""
stdin2, stdout2, stderr2 = client.exec_command(cmd2)
out2 = stdout2.read().decode('utf-8', errors='replace')

client.close()

print("TEST 1 (10-digit no suffix):", out1)
print("TEST 2 (8-digit no suffix):", out2)
