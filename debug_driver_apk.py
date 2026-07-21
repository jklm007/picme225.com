import paramiko
import os

hostname = '109.199.123.69'
username = 'root'
password = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(hostname, username=username, password=password)

cmd = """
POD=$(kubectl get pods -l app=laravel --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers | head -n 1)
kubectl exec default/$POD -- ls -lh public/apk/
kubectl cp default/$POD:/app/public/apk/picme-driver.apk /tmp/picme-driver.apk
"""
stdin, stdout, stderr = client.exec_command(cmd)
print("Out:", stdout.read().decode())
print("Err:", stderr.read().decode())

client.close()
