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
    
    # Write file using kubectl cp approach
    test_php = '<?php header("Set-Cookie: debugcookie=1; Path=/"); echo $_SERVER["HTTPS"] ?? "NO_HTTPS"; echo " | "; echo $_SERVER["HTTP_X_FORWARDED_PROTO"] ?? "NO_PROTO";'
    
    # Write via exec + python3
    cmd_write = f"""kubectl exec deployment/laravel-deployment -- bash -c 'echo "{test_php}" > /app/public/dt.php && chmod 644 /app/public/dt.php'"""
    stdin1, stdout1, stderr1 = client.exec_command(cmd_write)
    stdout1.read()
    
    # Hit it
    cmd_hit = f"curl -s -D - -H 'Host: picme225.site' http://{pod_ip}/dt.php"
    stdin2, stdout2, stderr2 = client.exec_command(cmd_hit)
    print("=== Raw PHP via Nginx ===")
    print(stdout2.read().decode('ascii', 'ignore'))
    
    # PHP-FPM pool config
    cmd_pool = "kubectl exec deployment/laravel-deployment -- cat /opt/docker/etc/php/fpm.conf 2>/dev/null || find /etc/php* /opt/docker/etc/php -name '*.conf' 2>/dev/null | head -10"
    stdin3, stdout3, stderr3 = client.exec_command(cmd_pool)
    print("\n=== PHP-FPM config files ===")
    print(stdout3.read().decode('ascii', 'ignore'))
    
    # PHP-FPM error log
    cmd_log = "kubectl exec deployment/laravel-deployment -- find /var/log -name '*php*' 2>/dev/null"
    stdin4, stdout4, stderr4 = client.exec_command(cmd_log)
    print("\n=== PHP log files ===")
    print(stdout4.read().decode('ascii', 'ignore'))
    
    # Cleanup
    client.exec_command("kubectl exec deployment/laravel-deployment -- rm -f /app/public/dt.php")
    
    client.close()

if __name__ == '__main__':
    main()
