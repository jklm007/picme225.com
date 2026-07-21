import paramiko

HOSTNAME = '109.199.123.69'
USERNAME = 'root'
PASSWORD = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOSTNAME, username=USERNAME, password=PASSWORD, timeout=30)

stdin, stdout, stderr = client.exec_command("kubectl get pods -l app=laravel --no-headers -o custom-columns=NAME:.metadata.name | head -n 1")
pod = stdout.read().decode().strip()
print(f"Pod: {pod}")

files = [
    '/app/resources/views/home.blade.php',
    '/app/resources/views/user/layout/app.blade.php',
    '/app/resources/views/drive.blade.php',
    '/app/resources/views/marketplace/detail.blade.php'
]

for f in files:
    stdin, stdout, stderr = client.exec_command(f"kubectl exec {pod} -- php -l {f}")
    print(f"Lint {f}: {stdout.read().decode().strip()} {stderr.read().decode().strip()}")

client.close()
