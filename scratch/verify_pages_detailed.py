import paramiko

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
    # Curl localhost inside pod to bypass external nginx/cloudflare SSL issues
    stdin, stdout, stderr = client.exec_command(
        f"kubectl exec {pod} -- curl -s -H 'Host: picme225.site' http://127.0.0.1{url} | wc -c"
    )
    size = stdout.read().decode('utf-8', errors='replace').strip()
    
    # Also get first 200 chars to see what it is
    stdin, stdout, stderr = client.exec_command(
        f"kubectl exec {pod} -- curl -s -H 'Host: picme225.site' http://127.0.0.1{url} | head -c 200"
    )
    snippet = stdout.read().decode('utf-8', errors='replace').strip()
    
    results.append(f"{name} ({url}): Size={size} bytes, Snippet={repr(snippet)}")

client.close()

output = "\n".join(results)
with open('scratch/verify_results_detailed.txt', 'w') as f:
    f.write(output)
print(output)
