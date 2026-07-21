import paramiko, sys
ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect("109.199.123.69", username="root", password="Charlotte23")
stdin, stdout, stderr = ssh.exec_command("cd /tmp/picme225-build && du -sh app resources routes config database public")
sys.stdout.buffer.write(stdout.read())
ssh.close()
