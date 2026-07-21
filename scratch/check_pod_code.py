import paramiko, time

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

pod = "laravel-deployment-56f54497f-r8pmg"

# Check if the think tag removal code is present on the pod
stdin, stdout, stderr = client.exec_command(f"kubectl exec {pod} -- grep -n 'think' /app/app/Jobs/ProcessWhatsappBatchJob.php")
print("Think tag on pod:")
print(stdout.read().decode())

# Check the line count
stdin, stdout, stderr = client.exec_command(f"kubectl exec {pod} -- wc -l /app/app/Jobs/ProcessWhatsappBatchJob.php")
print("Line count on pod:")
print(stdout.read().decode())

client.close()
