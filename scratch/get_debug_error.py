import paramiko
import re

client=paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

pod = "laravel-deployment-b5fb86954-cgldw"

# Enable APP_DEBUG
client.exec_command(f'kubectl exec {pod} -- sed -i "s/APP_DEBUG=false/APP_DEBUG=true/g" .env')
client.exec_command(f'kubectl exec {pod} -- php artisan config:clear')

# Curl the homepage
_, out, err = client.exec_command(f'kubectl exec {pod} -- curl -s http://127.0.0.1/')
html = out.read().decode('utf-8', errors='replace')

# Disable APP_DEBUG
client.exec_command(f'kubectl exec {pod} -- sed -i "s/APP_DEBUG=true/APP_DEBUG=false/g" .env')
client.exec_command(f'kubectl exec {pod} -- php artisan config:clear')

# Parse error
exc_match = re.search(r'class="exception_title">\s*(.*?)\s*</', html, re.IGNORECASE | re.DOTALL)
if exc_match:
    print("Exception:", exc_match.group(1).strip())
    msg_match = re.search(r'class="exception_message">\s*(.*?)\s*</', html, re.IGNORECASE | re.DOTALL)
    if msg_match:
        print("Message:", msg_match.group(1).strip())
else:
    print(html[:1000])

