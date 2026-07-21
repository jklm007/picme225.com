import paramiko

def main():
    hostname = '109.199.123.69'
    username = 'root'
    password = 'Charlotte23'
    
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(hostname, username=username, password=password)
    
    files_to_check = [
        '/opt/docker/etc/nginx/php.conf',
        '/opt/docker/etc/nginx/vhost.common.conf',
        '/opt/docker/etc/nginx/conf.d/10-php.conf',
        '/opt/docker/etc/nginx/global.conf',
        '/opt/docker/etc/nginx/main.conf',
        '/etc/nginx/conf.d/10-docker.conf',
        '/etc/nginx/nginx.conf',
    ]
    
    for f in files_to_check:
        cmd = f"kubectl exec deployment/laravel-deployment -- cat {f} 2>/dev/null"
        stdin, stdout, stderr = client.exec_command(cmd)
        content = stdout.read().decode('ascii', 'ignore').strip()
        if content:
            print(f"\n=== {f} ===")
            print(content[:1500])
    
    client.close()

if __name__ == '__main__':
    main()
