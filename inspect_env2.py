import paramiko

def main():
    hostname = '109.199.123.69'
    username = 'root'
    password = 'Charlotte23'
    
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(hostname, username=username, password=password)
    
    commands = """
    kubectl exec deployment/laravel-deployment -- head -n 15 /app/.env
    """
    
    stdin, stdout, stderr = client.exec_command(commands)
    print("Stdout:", stdout.read().decode('ascii', 'ignore'))
    client.close()

if __name__ == '__main__':
    main()
