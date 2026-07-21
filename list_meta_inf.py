import paramiko

hostname = '109.199.123.69'
username = 'root'
password = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(hostname, username=username, password=password)

# List META-INF files for User APK
print("=== USER APK META-INF ===")
cmd_user = """kubectl exec deploy/laravel-deployment -- unzip -l public/apk/picme-user.apk | grep META-INF/"""
stdin, stdout, stderr = client.exec_command(cmd_user)
print(stdout.read().decode('utf-8', errors='replace'))

client.close()
