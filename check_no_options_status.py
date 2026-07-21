import paramiko

hostname = '109.199.123.69'
username = 'root'
password = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(hostname, username=username, password=password)

# Check laravel logs for the message ID 3EB09E3A51BA6D1AA2AF2F
cmd = """kubectl logs --tail=1000 deploy/laravel-deployment | grep "3EB09E3A51BA6D1AA2AF2F" || true"""
stdin, stdout, stderr = client.exec_command(cmd)
out = stdout.read().decode('utf-8', errors='replace')

client.close()

with open("test_no_options_status.txt", "w", encoding="utf-8") as f:
    f.write(out)

print("Status logs written to test_no_options_status.txt")
