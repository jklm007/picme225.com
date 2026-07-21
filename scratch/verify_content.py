import paramiko

HOSTNAME = '109.199.123.69'
USERNAME = 'root'
PASSWORD = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOSTNAME, username=USERNAME, password=PASSWORD, timeout=30)

stdin, stdout, stderr = client.exec_command("kubectl get pods -l app=laravel --no-headers -o custom-columns=NAME:.metadata.name | head -n 1")
pod = stdout.read().decode().strip()

# Follow redirects and measure real page size
stdin, stdout, stderr = client.exec_command(
    f"kubectl exec {pod} -- curl -sL -H 'Host: picme225.site' http://127.0.0.1/ | wc -c"
)
size = stdout.read().decode().strip()
print(f"Homepage size (following redirects): {size} bytes")

# Check the first 500 chars with redirect follow
stdin, stdout, stderr = client.exec_command(
    f"kubectl exec {pod} -- curl -sL -H 'Host: picme225.site' http://127.0.0.1/ 2>&1 | head -c 500"
)
snippet = stdout.read().decode('utf-8', errors='replace')
print(f"HTML snippet:\n{snippet}")

# Also test via HTTPS with insecure flag
stdin, stdout, stderr = client.exec_command(
    f"kubectl exec {pod} -- curl -sk -o /dev/null -w '%{{http_code}}' https://127.0.0.1/"
)
https_code = stdout.read().decode().strip()
print(f"HTTPS status: {https_code}")

client.close()
