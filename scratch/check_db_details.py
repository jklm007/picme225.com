import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

# Get running laravel pod
stdin, stdout, stderr = client.exec_command("kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}'")
pod = stdout.read().decode().strip().strip("'")

# Run DB query for listings
query = "SELECT id, title, cover_image, images FROM marketplace_listings ORDER BY id DESC LIMIT 5;"
cmd = f"kubectl exec {pod} -- env PGPASSWORD='secret_password' psql -h postgres-service -U picme_user -d picme_db -c \"{query}\""
stdin, stdout, stderr = client.exec_command(cmd)
print("=== Marketplace Listings in Database ===")
print(stdout.read().decode())

client.close()
