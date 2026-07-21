import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

# Get running laravel pod to route to db
stdin, stdout, stderr = client.exec_command("kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}'")
pod = stdout.read().decode().strip().strip("'")

# Print all settings
stdin, stdout, stderr = client.exec_command(
    f"kubectl exec deployment/postgres -- psql -U picme_user -d picme_db -c \"SELECT key, value FROM settings ORDER BY key;\""
)
print("=== All Settings in Database ===")
print(stdout.read().decode())

client.close()
