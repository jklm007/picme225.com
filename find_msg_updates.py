import paramiko

hostname = '109.199.123.69'
username = 'root'
password = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(hostname, username=username, password=password)

# Query Kubernetes logs to find all messages.update events and print their statuses
cmd = """kubectl logs --tail=10000 deploy/laravel-deployment | grep -E "messages.update" | tail -n 100"""
stdin, stdout, stderr = client.exec_command(cmd)
out = stdout.read().decode('utf-8', errors='replace')

client.close()

with open("messages_updates.log", "w", encoding="utf-8") as f:
    f.write(out)

print("Updates written to messages_updates.log")
