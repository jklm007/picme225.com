import paramiko, time

hostname = '109.199.123.69'
username = 'root'
password = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(hostname, username=username, password=password)

# Send message to 2250714950219@s.whatsapp.net (10-digit format) after instance restart
cmd = """kubectl exec deploy/laravel-deployment -- curl -s -X POST http://evolution-api-service:8080/message/sendText/picme_whatsapp -H "apikey: picme225-evolution-secret-key" -H "Content-Type: application/json" -d '{"number":"2250714950219@s.whatsapp.net","text":"Test après redémarrage instance"}'"""
stdin, stdout, stderr = client.exec_command(cmd)
out = stdout.read().decode('utf-8', errors='replace')
print("SEND RESULT:", out)

# Wait a few seconds
time.sleep(5)

# Check laravel logs for updates
cmd_logs = """kubectl logs --tail=100 deploy/laravel-deployment | grep "2250714950219" | tail -n 10 || true"""
stdin_logs, stdout_logs, stderr_logs = client.exec_command(cmd_logs)
out_logs = stdout_logs.read().decode('utf-8', errors='replace')
print("LARAVEL LOGS:")
print(out_logs)

client.close()
