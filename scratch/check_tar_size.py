import paramiko, sys
ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect("109.199.123.69", username="root", password="Charlotte23")
stdin, stdout, stderr = ssh.exec_command("ls -lh /tmp/picme_prod.tar.gz")
print("Server tar size:", stdout.read().decode())
ssh.close()
