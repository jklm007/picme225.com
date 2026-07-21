import paramiko

def main():
    hostname = '109.199.123.69'
    username = 'root'
    password = 'Charlotte23'
    
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(hostname, username=username, password=password)
    
    # Check ingress config
    cmd1 = "kubectl get ingress -A"
    stdin, stdout, stderr = client.exec_command(cmd1)
    print("=== INGRESS ===")
    print(stdout.read().decode('ascii', 'ignore'))
    
    # Check ingress annotations
    cmd2 = "kubectl get ingress -A -o yaml | head -n 60"
    stdin2, stdout2, stderr2 = client.exec_command(cmd2)
    print("=== INGRESS YAML ===")
    print(stdout2.read().decode('ascii', 'ignore'))
    
    client.close()

if __name__ == '__main__':
    main()
