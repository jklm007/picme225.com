import paramiko

hostname = '109.199.123.69'
username = 'root'
password = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(hostname, username=username, password=password)

# Check the file size of the APKs in the public/uploads/apk directory
cmd = """kubectl exec deploy/laravel-deployment -- ls -lh public/uploads/apk/"""
stdin, stdout, stderr = client.exec_command(cmd)
print("APK FILES:")
print(stdout.read().decode('utf-8', errors='replace'))
print("Errors (if any):")
print(stderr.read().decode('utf-8', errors='replace'))

client.close()
