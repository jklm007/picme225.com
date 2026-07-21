import paramiko

def main():
    hostname = '109.199.123.69'
    username = 'root'
    password = 'Charlotte23'
    
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(hostname, username=username, password=password)
    
    pod_ip_cmd = "kubectl get pod -l app=laravel -o jsonpath='{.items[0].status.podIP}'"
    stdin, stdout, stderr = client.exec_command(pod_ip_cmd)
    pod_ip = stdout.read().decode('ascii', 'ignore').strip().strip("'")
    
    # Create a test PHP file that dumps all server variables and sends a Set-Cookie
    dump_script = r"""<?php
header('Set-Cookie: debugcookie=1; Path=/');
header('Content-Type: text/plain');
echo "HTTPS=" . (isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : 'NOT SET') . "\n";
echo "HTTP_X_FORWARDED_PROTO=" . (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) ? $_SERVER['HTTP_X_FORWARDED_PROTO'] : 'NOT SET') . "\n";
echo "SERVER_PORT=" . $_SERVER['SERVER_PORT'] . "\n";
echo "HTTP_HOST=" . $_SERVER['HTTP_HOST'] . "\n";
echo "HTTP_COOKIE=" . (isset($_SERVER['HTTP_COOKIE']) ? $_SERVER['HTTP_COOKIE'] : 'NONE') . "\n";
echo "REQUEST_SCHEME=" . (isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'NOT SET') . "\n";
"""
    
    cmd1 = f"""kubectl exec deployment/laravel-deployment -- bash -c "cat > /app/public/debugtest.php << 'PHPEOF'\n{dump_script}\nPHPEOF" """
    stdin1, stdout1, stderr1 = client.exec_command(cmd1)
    stdout1.read()
    
    # Hit via nginx
    cmd2 = f"""curl -s -D - -H 'Host: picme225.site' -H 'X-Forwarded-Proto: https' 'http://{pod_ip}/debugtest.php' 2>&1"""
    stdin2, stdout2, stderr2 = client.exec_command(cmd2)
    print("=== PHP Debug via Nginx ===")
    print(stdout2.read().decode('ascii', 'ignore'))
    
    # Now try hitting /admin/login and check what happens (look for session_start errors in php-fpm log)
    cmd3 = "kubectl exec deployment/laravel-deployment -- tail -20 /var/log/php-fpm.log 2>/dev/null || kubectl exec deployment/laravel-deployment -- tail -20 /var/log/php8.4-fpm.log 2>/dev/null"
    stdin3, stdout3, stderr3 = client.exec_command(cmd3)
    print("=== PHP-FPM log ===")
    print(stdout3.read().decode('ascii', 'ignore'))
    
    # Cleanup
    cmd4 = "kubectl exec deployment/laravel-deployment -- rm -f /app/public/debugtest.php"
    client.exec_command(cmd4)
    
    client.close()

if __name__ == '__main__':
    main()
