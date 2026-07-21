import paramiko

def main():
    hostname = '109.199.123.69'
    username = 'root'
    password = 'Charlotte23'
    
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(hostname, username=username, password=password)
    
    # 1. Modify index.php
    cmd = """kubectl exec deployment/laravel-deployment -- sed -i 's/$response->send();/if (headers_sent($file, $line)) { file_put_contents("\\/app\\/storage\\/logs\\/cookie_debug.log", "Headers already sent in $file on line $line"); } else { file_put_contents("\\/app\\/storage\\/logs\\/cookie_debug.log", "Headers NOT sent yet."); } $response->send();/' /app/public/index.php"""
    client.exec_command(cmd)
    
    # 2. Hit the page
    pod_ip_cmd = "kubectl get pod -l app=laravel -o jsonpath='{.items[0].status.podIP}'"
    stdin, stdout, stderr = client.exec_command(pod_ip_cmd)
    pod_ip = stdout.read().decode('ascii', 'ignore').strip().strip("'")
    
    client.exec_command(f"curl -s http://{pod_ip}/admin/login")
    
    # 3. Read the log
    cmd = "kubectl exec deployment/laravel-deployment -- cat /app/storage/logs/cookie_debug.log"
    stdin, stdout, stderr = client.exec_command(cmd)
    print("=== Headers sent status ===")
    print(stdout.read().decode('ascii', 'ignore'))
    
    # 4. Revert index.php
    client.exec_command("kubectl exec deployment/laravel-deployment -- git checkout -- /app/public/index.php")
    
    client.close()

if __name__ == '__main__':
    main()
