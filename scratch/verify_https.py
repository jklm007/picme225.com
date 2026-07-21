import paramiko

HOSTNAME = '109.199.123.69'
USERNAME = 'root'
PASSWORD = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOSTNAME, username=USERNAME, password=PASSWORD, timeout=30)

stdin, stdout, stderr = client.exec_command("kubectl get pods -l app=laravel --no-headers -o custom-columns=NAME:.metadata.name | head -n 1")
pod = stdout.read().decode().strip()

# Get HTTPS content with correct host header and save to file
stdin, stdout, stderr = client.exec_command(
    f"kubectl exec {pod} -- curl -sk -H 'Host: picme225.site' https://127.0.0.1/ | wc -c"
)
size = stdout.read().decode().strip()
print(f"HTTPS homepage size: {size} bytes")

# First 1000 chars
stdin, stdout, stderr = client.exec_command(
    f"kubectl exec {pod} -- curl -sk -H 'Host: picme225.site' https://127.0.0.1/ 2>&1 | head -c 1000"
)
snippet = stdout.read().decode('utf-8', errors='replace')
print(f"Content start:\n{snippet}")

# Check if the error is in the page
if 'error' in snippet.lower() or 'exception' in snippet.lower() or 'warning' in snippet.lower():
    print("\n!!! ERROR DETECTED IN PAGE !!!")
elif '<html' in snippet.lower() or '<!doctype' in snippet.lower():
    print("\nSite is rendering HTML correctly!")
else:
    print(f"\nUnexpected content: {snippet}")

client.close()
