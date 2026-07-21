import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

# 1. Check env vars
print("=" * 60)
print("ENV GROQ VARS")
print("=" * 60)
cmd = "kubectl exec $(kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}') -- env | grep -E 'GROQ|GROQ_MODEL|GROQ_TEXT|GROQ_VISION'"
stdin, stdout, stderr = client.exec_command(cmd)
print(stdout.read().decode())

# 2. Check all PHP files with old model references
print("=" * 60)
print("PHP FILES WITH OLD MODELS")
print("=" * 60)
cmd = "kubectl exec $(kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}') -- grep -r 'mixtral' /app --include='*.php' -l 2>/dev/null"
stdin, stdout, stderr = client.exec_command(cmd)
print(stdout.read().decode())

cmd = "kubectl exec $(kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}') -- grep -r 'mixtral' /app --include='*.php' 2>/dev/null"
stdin, stdout, stderr = client.exec_command(cmd)
print(stdout.read().decode())

# 3. Check .env file
print("=" * 60)
print(".ENV GROQ VARS")
print("=" * 60)
cmd = "kubectl exec $(kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}') -- cat /app/.env | grep -i groq"
stdin, stdout, stderr = client.exec_command(cmd)
print(stdout.read().decode())

# 4. Check config/services.php
print("=" * 60)
print("config/services.php GROQ SECTION")
print("=" * 60)
cmd = "kubectl exec $(kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}') -- cat /app/config/services.php | grep -A 10 groq"
stdin, stdout, stderr = client.exec_command(cmd)
print(stdout.read().decode())

client.close()
