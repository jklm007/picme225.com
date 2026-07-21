import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

# Get running laravel pod
stdin, stdout, stderr = client.exec_command("kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}'")
pod = stdout.read().decode().strip().strip("'")
print(f"Laravel Pod: {pod}")

# Print env variables of storage
stdin, stdout, stderr = client.exec_command(f"kubectl exec {pod} -- env | grep -iE 'disk|storage|s3|r2|aws'")
print("=== Laravel Pod Env Variables ===")
print(stdout.read().decode())

# Print .env storage/public url lines
stdin, stdout, stderr = client.exec_command(f"kubectl exec {pod} -- grep -E 'URL|DISK|DRIVER|BUCKET|R2|AWS' .env")
print("=== Laravel Pod .env Storage Config ===")
print(stdout.read().decode())

# Check Settings table in DB for R2/AWS settings
stdin, stdout, stderr = client.exec_command(
    f"kubectl exec deployment/postgres -- psql -U picme_user -d picme_db -c \"SELECT * FROM settings WHERE key LIKE 'r2%' OR key LIKE 'aws%' OR key LIKE 'disk%' OR key LIKE 'media%';\""
)
print("=== Settings in Database ===")
print(stdout.read().decode())

client.close()
