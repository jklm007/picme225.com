import paramiko

def main():
    hostname = '109.199.123.69'
    username = 'root'
    password = 'Charlotte23'
    
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(hostname, username=username, password=password)
    
    # Search ALL nginx conf for hide_header or cookie filtering
    cmd1 = "kubectl exec deployment/laravel-deployment -- find /opt/docker/etc/nginx /etc/nginx -name '*.conf' 2>/dev/null"
    stdin, stdout, stderr = client.exec_command(cmd1)
    files = stdout.read().decode('ascii', 'ignore').strip().split('\n')
    print("=== Nginx config files ===")
    for f in files:
        print(f)
    
    # Search for fastcgi_hide_header or proxy_hide_header
    cmd2 = r"kubectl exec deployment/laravel-deployment -- grep -r 'hide_header\|fastcgi_cache\|fastcgi_ignore' /opt/docker/etc/nginx/ /etc/nginx/ 2>/dev/null"
    stdin2, stdout2, stderr2 = client.exec_command(cmd2)
    result = stdout2.read().decode('ascii', 'ignore')
    print("\n=== hide_header / cache directives ===")
    print(result if result else "(none found)")
    
    # Check the main webdevops nginx.conf
    cmd3 = "kubectl exec deployment/laravel-deployment -- cat /opt/docker/etc/nginx/nginx.conf 2>/dev/null || cat /etc/nginx/nginx.conf"
    stdin3, stdout3, stderr3 = client.exec_command(cmd3)
    print("\n=== nginx.conf ===")
    print(stdout3.read().decode('ascii', 'ignore')[:3000])
    
    client.close()

if __name__ == '__main__':
    main()
