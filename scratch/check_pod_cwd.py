import paramiko
ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect("109.199.123.69", username="root", password="Charlotte23")
cmd = "kubectl exec deploy/laravel-deployment -- pwd"
stdin, stdout, stderr = ssh.exec_command(cmd)
print("PWD:", stdout.read().decode("utf-8"))
cmd2 = "kubectl exec deploy/laravel-deployment -- ls -l"
stdin, stdout, stderr = ssh.exec_command(cmd2)
print("LS:", stdout.read().decode("utf-8")[:300])
ssh.close()
