import paramiko

def main():
    hostname = '109.199.123.69'
    username = 'root'
    password = 'Charlotte23'
    
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(hostname, username=username, password=password)
    
    # Check vhost.common.d files for X-Forwarded-Proto handling
    cmd1 = "kubectl exec deployment/laravel-deployment -- ls /opt/docker/etc/nginx/vhost.common.d/"
    stdin, stdout, stderr = client.exec_command(cmd1)
    print("=== vhost.common.d/ files ===")
    files = stdout.read().decode('ascii', 'ignore')
    print(files)
    
    # Check each file content
    for f in files.strip().split('\n'):
        f = f.strip()
        if f:
            cmd2 = f"kubectl exec deployment/laravel-deployment -- cat /opt/docker/etc/nginx/vhost.common.d/{f}"
            stdin2, stdout2, stderr2 = client.exec_command(cmd2)
            print(f"\n=== {f} ===")
            print(stdout2.read().decode('ascii', 'ignore'))
    
    client.close()

if __name__ == '__main__':
    main()
