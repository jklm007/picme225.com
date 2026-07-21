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

# Let's inspect HomeController.php index method
stdin, stdout, stderr = client.exec_command(f"kubectl exec {pod} -- grep -n -C 15 'public function index' /app/app/Http/Controllers/HomeController.php")
print(stdout.read().decode('utf-8', errors='replace'))

client.close()
