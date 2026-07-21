import paramiko

hostname = '109.199.123.69'
username = 'root'
password = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(hostname, username=username, password=password)

# Read the generated private and public key files from the pod
cmd1 = """kubectl exec deploy/laravel-deployment -- cat storage/oauth-private.key"""
stdin1, stdout1, stderr1 = client.exec_command(cmd1)
private_key = stdout1.read().decode('utf-8', errors='replace')

cmd2 = """kubectl exec deploy/laravel-deployment -- cat storage/oauth-public.key"""
stdin2, stdout2, stderr2 = client.exec_command(cmd2)
public_key = stdout2.read().decode('utf-8', errors='replace')

client.close()

print("--- PRIVATE KEY ---")
print(private_key)
print("--- PUBLIC KEY ---")
print(public_key)
