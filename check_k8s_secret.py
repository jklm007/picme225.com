import paramiko, json, base64

hostname = '109.199.123.69'
username = 'root'
password = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(hostname, username=username, password=password)

# Fetch current secret laravel-env in JSON format
cmd = """kubectl get secret laravel-env -o json"""
stdin, stdout, stderr = client.exec_command(cmd)
res = stdout.read().decode('utf-8', errors='replace')

try:
    secret_data = json.loads(res)
    keys = list(secret_data.get("data", {}).keys())
    print("KEYS IN SECRET:", keys)
except Exception as e:
    print("Error parsing secret JSON:", e)
    print("Raw Response:", res)

client.close()
