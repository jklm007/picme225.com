import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

sftp = client.open_sftp()
sftp.put('scratch/check_broken_images_fixed.php', '/tmp/check_broken_images_fixed.php')
sftp.close()

stdin, stdout, stderr = client.exec_command("kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}'")
pod = stdout.read().decode().strip().strip("'")

client.exec_command(f"kubectl cp /tmp/check_broken_images_fixed.php {pod}:/app/check_broken_images_fixed.php")
stdin, stdout, stderr = client.exec_command(f"kubectl exec {pod} -- php /app/check_broken_images_fixed.php")

print("=== Output ===")
print(stdout.read().decode())
err = stderr.read().decode()
if err:
    print("=== Error ===")
    print(err)

client.close()
