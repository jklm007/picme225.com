import paramiko
ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect("109.199.123.69", username="root", password="Charlotte23")
cmd = "kubectl exec deploy/laravel-deployment -- cat /app/resources/views/user/dashboard.blade.php > /tmp/dashboard_pod.blade.php"
ssh.exec_command(cmd)

sftp = ssh.open_sftp()
sftp.get("/tmp/dashboard_pod.blade.php", "scratch/dashboard_pod.blade.php")
sftp.close()
ssh.close()
