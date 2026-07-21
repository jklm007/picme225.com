import paramiko
import sys

HOSTNAME = '109.199.123.69'
USERNAME = 'root'
PASSWORD = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOSTNAME, username=USERNAME, password=PASSWORD, timeout=30)

stdin, stdout, stderr = client.exec_command("kubectl get pods -l app=laravel --no-headers -o custom-columns=NAME:.metadata.name | head -n 1")
pod = stdout.read().decode().strip()

pages = [
    ('/', 'Home'),
    ('/drive', 'Drive'),
    ('/marketplace', 'Marketplace'),
    ('/login', 'Login'),
]

results = []
for url, name in pages:
    stdin, stdout, stderr = client.exec_command(
        f"kubectl exec {pod} -- curl -s -o /dev/null -w '%{{http_code}}' -H 'Host: picme225.site' http://127.0.0.1{url}"
    )
    code = stdout.read().decode('utf-8', errors='replace').strip()
    ok = "OK" if code in ['200', '301', '302'] else "FAIL"
    results.append(f"  [{ok}] {name} ({url}): HTTP {code}")

stdin, stdout, stderr = client.exec_command(
    f"kubectl exec {pod} -- curl -s -H 'Host: picme225.site' http://127.0.0.1/ | wc -c"
)
size = stdout.read().decode().strip()
results.append(f"\n  Homepage size: {size} bytes")

client.close()

output = "\n".join(results)
with open('scratch/verify_results.txt', 'w') as f:
    f.write(output)
print(output)
