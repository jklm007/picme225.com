import paramiko

client=paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

cmd = "kubectl exec laravel-deployment-b5fb86954-cgldw -- tail -n 100 storage/logs/laravel.log"
_, out, err = client.exec_command(cmd)
log = out.read().decode('utf-8', errors='replace')

lines = log.split('\n')
for line in lines:
    if "ERROR" in line or "Exception" in line or "ParseError" in line or "Fatal" in line:
        print(line)
