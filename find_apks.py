import paramiko

hostname = '109.199.123.69'
username = 'root'
password = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(hostname, username=username, password=password)

# Search for any APK files in the public directory
cmd = """kubectl exec deploy/laravel-deployment -- find public/ -name "*.apk" -exec ls -lh {} +"""
stdin, stdout, stderr = client.exec_command(cmd)
print("APK FILES FOUND:")
print(stdout.read().decode('utf-8', errors='replace'))

client.close()
