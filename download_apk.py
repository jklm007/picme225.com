import paramiko
import os

hostname = '109.199.123.69'
username = 'root'
password = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(hostname, username=username, password=password)

print("Copying APK from pod to server /tmp...")
cmd = """
POD=$(kubectl get pods -l app=laravel --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers | head -n 1)
kubectl cp default/$POD:/app/public/apk/picme-user.apk /tmp/picme-user.apk
"""
stdin, stdout, stderr = client.exec_command(cmd)
print("Out:", stdout.read().decode())
print("Err:", stderr.read().decode())

print("Downloading APK to local machine via SFTP...")
sftp = client.open_sftp()
local_path = os.path.join(os.getcwd(), 'picme-user-unsigned.apk')
sftp.get('/tmp/picme-user.apk', local_path)
sftp.close()

client.close()
print("Download complete: " + local_path)
