import paramiko

HOSTNAME = '109.199.123.69'
USERNAME = 'root'
PASSWORD = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOSTNAME, username=USERNAME, password=PASSWORD, timeout=30)

def run(cmd):
    stdin, stdout, stderr = client.exec_command(cmd)
    return stdout.read().decode('utf-8', errors='replace').strip()

print("==== KUBECTL GET PODS ====")
print(run("kubectl get pods -A"))
print("==== KUBECTL GET DEPLOYMENTS ====")
print(run("kubectl get deployments -A"))
print("==== KUBECTL GET INGRESS ====")
print(run("kubectl get ingress -A"))
print("==== DOCKER PS ====")
print(run("docker ps"))

client.close()
