import paramiko

def main():
    hostname = '109.199.123.69'
    username = 'root'
    password = 'Charlotte23'
    
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(hostname, username=username, password=password)
    
    # Get latest laravel log
    commands = """
    kubectl exec deployment/laravel-deployment -- tail -n 80 /app/storage/logs/laravel.log
    """
    
    stdin, stdout, stderr = client.exec_command(commands)
    output = stdout.read().decode('ascii', 'ignore')
    print(output[-3000:] if len(output) > 3000 else output)
    client.close()

if __name__ == '__main__':
    main()
