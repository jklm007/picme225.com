import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

stdin, stdout, stderr = client.exec_command("kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}'")
pod = stdout.read().decode().strip().strip("'")

stdin, stdout, stderr = client.exec_command(f"kubectl exec {pod} -- cat /app/app/Http/Controllers/Admin/MarketplaceListingController.php | grep -A 5 'cover_image'")
print("On Pod:")
print(stdout.read().decode())
client.close()
