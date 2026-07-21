import paramiko

hostname = '109.199.123.69'
username = 'root'
password = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(hostname, username=username, password=password)

# Check evolution-api logs for our test message ID or the phone number
cmd = """kubectl logs --tail=10000 deploy/evolution-api-deployment | grep -A 10 -B 2 "3EB0E57879ADC8ABEC956A" || true"""
stdin, stdout, stderr = client.exec_command(cmd)
out = stdout.read().decode('utf-8', errors='replace')

client.close()

with open("test_msg_logs.txt", "w", encoding="utf-8") as f:
    f.write(out)

print("Logs written to test_msg_logs.txt")
