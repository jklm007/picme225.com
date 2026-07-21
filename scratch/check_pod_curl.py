import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

stdin, stdout, stderr = client.exec_command("kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}'")
pod = stdout.read().decode().strip().strip("'")

# Run curl inside pod
cmd = f"kubectl exec {pod} -- curl -I http://localhost/storage/listings/1b8c525a-e78d-48e0-861d-5e379d1e6875_1784099071.webp"
stdin, stdout, stderr = client.exec_command(cmd)
print("=== Output ===")
print(stdout.read().decode())
err = stderr.read().decode()
if err:
    print("=== Error ===")
    print(err)

client.close()
