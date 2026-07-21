import paramiko

hostname = '109.199.123.69'
client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(hostname, username='root', password='Charlotte23')

cmd = """
curl -I -s https://www.picme225.site/storage/listings/8c566f95-c9ea-4e4d-9831-f5692bd8bf23_1783501347.webp
echo "==="
curl -I -s https://picme225.site/storage/listings/8c566f95-c9ea-4e4d-9831-f5692bd8bf23_1783501347.webp
"""

stdin, stdout, stderr = client.exec_command(cmd)
print('Out:', stdout.read().decode())
print('Err:', stderr.read().decode())
client.close()
