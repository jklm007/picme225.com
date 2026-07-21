import paramiko

def main():
    hostname = '109.199.123.69'
    username = 'root'
    password = 'Charlotte23'
    
    print(f"Connecting to {hostname}...")
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(hostname, username=username, password=password)
    
    # We want to check the logs of the laravel pod
    commands = """
    kubectl get pods | grep laravel-deployment
    kubectl logs deployment/laravel-deployment --tail=100
    """
    
    stdin, stdout, stderr = client.exec_command(commands)
    
    print("Server output:")
    try:
        for line in iter(stdout.readline, ""):
            print(line.encode('ascii', 'ignore').decode('ascii'), end="")
    except Exception as e:
        print("Error:", e)
        
    client.close()

if __name__ == '__main__':
    main()
