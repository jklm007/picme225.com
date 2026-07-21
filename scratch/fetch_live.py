import paramiko

HOSTNAME = '109.199.123.69'
USERNAME = 'root'
PASSWORD = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOSTNAME, username=USERNAME, password=PASSWORD, timeout=30)

# Curl the live site from the server itself to get real content
print("=== Fetching live site content from server ===")
stdin, stdout, stderr = client.exec_command(
    "curl -sk https://picme225.site/ 2>&1 | head -c 3000"
)
html = stdout.read().decode('utf-8', errors='replace')
with open('scratch/live_site.html', 'w', encoding='utf-8') as f:
    f.write(html)
print(f"Content length: {len(html)} chars")
print(f"First 2000 chars:\n{html[:2000]}")

# Check for <body> content
if '<body' in html.lower():
    body_start = html.lower().find('<body')
    print(f"\nBody tag found at position {body_start}")
    print(f"Content after body: {html[body_start:body_start+300]}")

# Check for JS errors indicators
if 'app.js' in html or 'app.min.js' in html:
    print("\nJS assets referenced in HTML")
if 'csrf-token' in html:
    print("CSRF token present - Laravel is rendering")

client.close()
