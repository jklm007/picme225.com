import paramiko

client=paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

cmd = "kubectl exec laravel-deployment-b5fb86954-cgldw -- tail -n 200 storage/logs/laravel.log"
_, out, err = client.exec_command(cmd)
log = out.read().decode('utf-8', errors='replace')

for i, line in enumerate(log.split('\n')):
    if "ERROR" in line or "Exception" in line or "Fatal" in line or "Trying to get property" in line:
        print("MATCH:", line)
        for j in range(max(0, i-2), min(len(log.split('\n')), i+5)):
            if j != i:
                print(f"  [{j}] {log.split(chr(10))[j]}")
        break
