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
    
    cmd = f"""curl -s -D - -H 'Host: picme225.site' -H 'X-Forwarded-Proto: https' http://{pod_ip}/admin/login | head -20"""
    stdin, stdout, stderr = client.exec_command(cmd)
    print("=== Raw headers from pod ===")
    print(stdout.read().decode('ascii', 'ignore'))
    
    client.close()

if __name__ == '__main__':
    main()
