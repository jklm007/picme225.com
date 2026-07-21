import paramiko

def main():
    hostname = '109.199.123.69'
    username = 'root'
    password = 'Charlotte23'
    
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(hostname, username=username, password=password)
    
    # Patch Nginx to explicitly pass Set-Cookie from FastCGI
    # Add fastcgi_pass_header Set-Cookie; to the PHP location block
    new_php_conf = """location ~ \\.php$ {
    fastcgi_split_path_info ^(.+\\.php)(/.+)$;
    fastcgi_pass php;
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME     $request_filename;
    fastcgi_read_timeout 600;
    fastcgi_pass_header Set-Cookie;
    fastcgi_pass_header Cookie;
    fastcgi_ignore_headers Cache-Control Expires Set-Cookie;
}
"""
    # Nope, fastcgi_ignore_headers would strip them. Let's just add fastcgi_pass_header
    new_php_conf = r"""location ~ \.php$ {
    fastcgi_split_path_info ^(.+\.php)(/.+)$;
    fastcgi_pass php;
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME     $request_filename;
    fastcgi_read_timeout 600;
}
"""
    
    # Check if nginx has fastcgi_cache enabled which might be caching responses without cookies
    cmd = "kubectl exec deployment/laravel-deployment -- nginx -T 2>/dev/null | head -100"
    stdin, stdout, stderr = client.exec_command(cmd)
    print(stdout.read().decode('ascii', 'ignore')[:3000])
    
    client.close()

if __name__ == '__main__':
    main()
