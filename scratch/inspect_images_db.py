import paramiko
client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

# Get pod
stdin, stdout, stderr = client.exec_command("kubectl get pod -l app=laravel -o jsonpath='{.items[0].metadata.name}'")
pod = stdout.read().decode().strip().strip("'")

# Exec tinker
cmd = f"kubectl exec {pod} -- php artisan tinker --execute=\"echo json_encode(DB::table('services')->select('id', 'name', 'image')->get());\""
stdin, stdout, stderr = client.exec_command(cmd)
print("Services: ", stdout.read().decode())

cmd2 = f"kubectl exec {pod} -- php artisan tinker --execute=\"echo json_encode(DB::table('service_types')->select('id', 'name', 'image')->get());\""
stdin, stdout, stderr = client.exec_command(cmd2)
print("ServiceTypes: ", stdout.read().decode())
