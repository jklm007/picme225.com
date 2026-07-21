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
    print("Pod IP:", pod_ip)
    
    # Create test file in the right public dir and ensure permissions
    cmd1 = r"""kubectl exec deployment/laravel-deployment -- bash -c "printf '<?php header(\"Set-Cookie: testcookie=testval; Path=/\"); echo \"OK\";' > /app/public/cookietest.php && chmod 644 /app/public/cookietest.php" """
    stdin1, stdout1, stderr1 = client.exec_command(cmd1)
    print("Create:", stdout1.read().decode('ascii','ignore'), stderr1.read().decode('ascii','ignore'))
    
    # Verify file exists
    cmd2 = "kubectl exec deployment/laravel-deployment -- ls -la /app/public/cookietest.php"
    stdin2, stdout2, stderr2 = client.exec_command(cmd2)
    print("File:", stdout2.read().decode('ascii','ignore'))
    
    # Hit via nginx directly (not Laravel's index.php)
    cmd3 = f"""curl -s -D - -H 'Host: picme225.site' 'http://{pod_ip}/cookietest.php' 2>&1"""
    stdin3, stdout3, stderr3 = client.exec_command(cmd3)
    print("Response:")
    print(stdout3.read().decode('ascii', 'ignore'))
    
    # Cleanup
    cmd4 = "kubectl exec deployment/laravel-deployment -- rm -f /app/public/cookietest.php"
    client.exec_command(cmd4)
    
    client.close()

if __name__ == '__main__':
    main()
