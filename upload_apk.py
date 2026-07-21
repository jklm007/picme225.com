import paramiko
import os

hostname = '109.199.123.69'
username = 'root'
password = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(hostname, username=username, password=password)

print("Uploading signed APK to server /tmp...")
sftp = client.open_sftp()
local_path = os.path.join(os.getcwd(), 'picme-user-signed.apk')
sftp.put(local_path, '/tmp/picme-user.apk')
sftp.close()

print("Copying APK from /tmp into the kubernetes pods...")
cmd = """
LARAVEL_PODS=$(kubectl get pods -l app=laravel --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers)
WORKER_PODS=$(kubectl get pods -l app=laravel-worker --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers)

for POD in $LARAVEL_PODS; do
    kubectl cp /tmp/picme-user.apk default/$POD:/app/public/apk/picme-user.apk
    echo "  -> Uploaded to web pod $POD"
done

for POD in $WORKER_PODS; do
    kubectl cp /tmp/picme-user.apk default/$POD:/app/public/apk/picme-user.apk
    echo "  -> Uploaded to worker pod $POD"
done
"""
stdin, stdout, stderr = client.exec_command(cmd)
print("Out:", stdout.read().decode())
print("Err:", stderr.read().decode())

client.close()
print("Upload and deploy complete!")
