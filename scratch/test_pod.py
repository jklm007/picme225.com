import paramiko

def run_ssh_command(host, user, password, command):
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    try:
        client.connect(host, username=user, password=password, timeout=10)
        stdin, stdout, stderr = client.exec_command(command)
        out = stdout.read().decode()
        err = stderr.read().decode()
        return out, err
    except Exception as e:
        return str(e), ""
    finally:
        client.close()

if __name__ == "__main__":
    yaml_content = """
apiVersion: v1
kind: Pod
metadata:
  name: laravel-test-old
  namespace: default
spec:
  containers:
  - name: laravel
    image: sha256:0583140a00997324d8fbae724d1dc95261a3651196bfb7a69c9b38aac066b1c6
    imagePullPolicy: Never
    command: ["sleep", "3600"]
  restartPolicy: Never
"""
    
    # Write yaml to server and create pod
    create_cmd = f"cat << 'EOF' > /tmp/test-pod.yaml\n{yaml_content}\nEOF\nk3s kubectl apply -f /tmp/test-pod.yaml"
    out, err = run_ssh_command('109.199.123.69', 'root', 'Charlotte23', create_cmd)
    print("CREATE POD:", out, err)
    
    # Wait for pod to be ready and execute ls
    wait_cmd = "k3s kubectl wait --for=condition=Ready pod/laravel-test-old --timeout=30s && k3s kubectl exec laravel-test-old -- ls -la /app/resources/views/user/"
    out, err = run_ssh_command('109.199.123.69', 'root', 'Charlotte23', wait_cmd)
    print("WAIT AND EXEC:", out, err)
