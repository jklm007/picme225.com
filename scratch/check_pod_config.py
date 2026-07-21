import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

stdin, stdout, stderr = client.exec_command("kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}'")
pod = stdout.read().decode().strip().strip("'")

# Run tinker
cmd = "kubectl exec {} -- php artisan tinker --execute=\"echo 'DISK: ' . config('filesystems.default') . '\\n'; echo 'APP_URL: ' . config('app.url') . '\\n'; echo 'ASSET_URL: ' . config('app.asset_url') . '\\n';\"".format(pod)
stdin, stdout, stderr = client.exec_command(cmd)
print(stdout.read().decode())
print(stderr.read().decode())

client.close()
