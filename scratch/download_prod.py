import paramiko, sys
ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect("109.199.123.69", username="root", password="Charlotte23")
print("Compressing core files on server...")
cmd = "cd /tmp/picme225-build && tar -czf /tmp/picme_core.tar.gz app resources routes config database public/css public/js composer.json package.json server.php artisan .env.example"
stdin, stdout, stderr = ssh.exec_command(cmd)
stdout.channel.recv_exit_status()
print(stderr.read().decode())

print("Downloading core files...")
sftp = ssh.open_sftp()
sftp.get("/tmp/picme_core.tar.gz", "C:\\\\Users\\\\HP\\\\Documents\\\\Jews-world Backend\\\\picme_core.tar.gz")
sftp.close()
ssh.close()
print("Done!")
