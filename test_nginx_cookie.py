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
    
    # Use strace to capture what nginx sends back from php-fpm
    # Instead, let's add a debug header via Nginx itself to test
    # Add a temporary test endpoint that writes Set-Cookie manually
    
    # Simpler: check if PHP-FPM is working properly by hitting a simple PHP test
    cmd = f"""kubectl exec deployment/laravel-deployment -- bash -c "echo '<?php header(\"Set-Cookie: testcookie=testval; Path=/\"); echo \"OK\";' > /app/public/cookietest.php" """
    stdin2, stdout2, stderr2 = client.exec_command(cmd)
    stdout2.read()
    
    # Now hit that test file via Nginx
    cmd2 = f"""curl -s -D - -H 'Host: picme225.site' http://{pod_ip}/cookietest.php"""
    stdin3, stdout3, stderr3 = client.exec_command(cmd2)
    print("=== Simple PHP cookie test via Nginx ===")
    print(stdout3.read().decode('ascii', 'ignore'))
    
    # Cleanup
    cmd3 = "kubectl exec deployment/laravel-deployment -- rm -f /app/public/cookietest.php"
    stdin4, stdout4, stderr4 = client.exec_command(cmd3)
    stdout4.read()
    
    client.close()

if __name__ == '__main__':
    main()
