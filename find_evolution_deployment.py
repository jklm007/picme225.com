import paramiko

hostname = '109.199.123.69'
username = 'root'
password = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(hostname, username=username, password=password)

# Check all pods in the cluster
cmd = """kubectl get pods -A"""
stdin, stdout, stderr = client.exec_command(cmd)
print("PODS:")
print(stdout.read().decode('utf-8', errors='replace'))

# Check if there are docker containers running on the host directly
cmd2 = """docker ps"""
stdin2, stdout2, stderr2 = client.exec_command(cmd2)
print("DOCKER PS:")
print(stdout2.read().decode('utf-8', errors='replace'))

client.close()
