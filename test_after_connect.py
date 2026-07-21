import paramiko, time

hostname = '109.199.123.69'
username = 'root'
password = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(hostname, username=username, password=password)

# Send message to 22558286571@s.whatsapp.net (Wife)
cmd1 = """kubectl exec deploy/laravel-deployment -- curl -s -X POST http://evolution-api-service:8080/message/sendText/picme_whatsapp -H "apikey: picme225-evolution-secret-key" -H "Content-Type: application/json" -d '{"number":"22558286571@s.whatsapp.net","text":"Test post-reconnexion (Femme)"}'"""
stdin1, stdout1, stderr1 = client.exec_command(cmd1)
out1 = stdout1.read().decode('utf-8', errors='replace')
print("WIFE SEND RESULT:", out1)

# Send message to 2250714950219@s.whatsapp.net (Son)
cmd2 = """kubectl exec deploy/laravel-deployment -- curl -s -X POST http://evolution-api-service:8080/message/sendText/picme_whatsapp -H "apikey: picme225-evolution-secret-key" -H "Content-Type: application/json" -d '{"number":"2250714950219@s.whatsapp.net","text":"Test post-reconnexion (Fils)"}'"""
stdin2, stdout2, stderr2 = client.exec_command(cmd2)
out2 = stdout2.read().decode('utf-8', errors='replace')
print("SON SEND RESULT:", out2)

# Wait a few seconds for webhooks
print("Waiting 6 seconds for webhook updates...")
time.sleep(6)

# Check last 100 lines of laravel logs for webhook updates of these numbers
cmd_logs = """kubectl logs --tail=100 deploy/laravel-deployment | grep -E "22558286571|2250714950219" | tail -n 10 || true"""
stdin_logs, stdout_logs, stderr_logs = client.exec_command(cmd_logs)
out_logs = stdout_logs.read().decode('utf-8', errors='replace')
print("LARAVEL LOGS:")
print(out_logs)

client.close()
