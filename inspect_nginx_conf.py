import paramiko

def main():
    hostname = '109.199.123.69'
    username = 'root'
    password = 'Charlotte23'
    
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(hostname, username=username, password=password)
    
    # Check Nginx config for fastcgi headers
    cmd1 = "kubectl exec deployment/laravel-deployment -- cat /opt/docker/etc/nginx/vhost.conf"
    stdin, stdout, stderr = client.exec_command(cmd1)
    out1 = stdout.read().decode('ascii', 'ignore')
    print("=== vhost.conf ===")
    print(out1[:3000])
    
    # Check the fastcgi_params
    cmd2 = "kubectl exec deployment/laravel-deployment -- cat /etc/nginx/fastcgi_params"
    stdin2, stdout2, stderr2 = client.exec_command(cmd2)
    out2 = stdout2.read().decode('ascii', 'ignore')
    print("=== fastcgi_params ===")
    print(out2[:2000])
    
    client.close()

if __name__ == '__main__':
    main()
