import paramiko

def main():
    hostname = '109.199.123.69'
    username = 'root'
    password = 'Charlotte23'
    
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(hostname, username=username, password=password)
    
    # Get the FULL nginx -T config
    cmd = "kubectl exec deployment/laravel-deployment -- nginx -T 2>/dev/null"
    stdin, stdout, stderr = client.exec_command(cmd)
    output = stdout.read().decode('ascii', 'ignore')
    
    # Save to file for analysis
    with open('nginx_full_config.txt', 'w', encoding='utf-8') as f:
        f.write(output)
    
    print(f"Total length: {len(output)} chars")
    # Print last 3000 chars which should have the vhost config
    print(output[-4000:])
    
    client.close()

if __name__ == '__main__':
    main()
