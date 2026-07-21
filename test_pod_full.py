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
    
    # Full verbose curl to pod with proper headers
    cmd = f"""curl -v -H 'Host: picme225.site' -H 'X-Forwarded-Proto: https' -H 'X-Forwarded-For: 1.2.3.4' http://{pod_ip}/admin/login 2>&1 | head -50"""
    stdin2, stdout2, stderr2 = client.exec_command(cmd)
    print(stdout2.read().decode('ascii', 'ignore'))
    
    client.close()

if __name__ == '__main__':
    main()
