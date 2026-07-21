import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

commands = """
set -e
echo "Removing old image from containerd..."
k3s crictl rmi docker.io/library/picme225-laravel:latest || true
echo "Importing new image..."
cd /tmp/picme225-build
k3s ctr -n k8s.io images import laravel.tar
echo "Restarting pods..."
kubectl delete pods -l app=laravel
kubectl delete pods -l app=laravel-worker
"""

stdin, stdout, stderr = client.exec_command(commands)
print(stdout.read().decode())
print(stderr.read().decode())
