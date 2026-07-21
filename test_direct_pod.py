import paramiko

def main():
    hostname = '109.199.123.69'
    username = 'root'
    password = 'Charlotte23'
    
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(hostname, username=username, password=password)
    
    # Get pod IP
    cmd_ip = "kubectl get pod -l app=laravel -o jsonpath='{.items[0].status.podIP}'"
    stdin, stdout, stderr = client.exec_command(cmd_ip)
    pod_ip = stdout.read().decode('ascii', 'ignore').strip().strip("'")
    print("Pod IP:", pod_ip)
    
    # Hit the pod directly (bypass Traefik), with Host header set
    cmd = f"""curl -s -v -H 'Host: picme225.site' -H 'X-Forwarded-Proto: https' http://{pod_ip}/admin/login 2>&1 | grep -i 'Set-Cookie\\|< HTTP'"""
    stdin2, stdout2, stderr2 = client.exec_command(cmd)
    print("Direct pod response headers:")
    print(stdout2.read().decode('ascii', 'ignore'))
    
    client.close()

if __name__ == '__main__':
    main()
