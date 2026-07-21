import paramiko
import time

def main():
    hostname = '109.199.123.69'
    username = 'root'
    password = 'Charlotte23'
    
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(hostname, username=username, password=password)
    
    # Check if the new pod has picked up REDIS_CLIENT
    cmd = "kubectl get pods -l app=laravel -o wide"
    stdin, stdout, stderr = client.exec_command(cmd)
    print("Pods:", stdout.read().decode('ascii', 'ignore'))
    
    # Wait for rollout to complete
    cmd2 = "kubectl rollout status deployment/laravel-deployment --timeout=60s"
    stdin2, stdout2, stderr2 = client.exec_command(cmd2)
    print("Rollout:", stdout2.read().decode('ascii', 'ignore'))
    
    # Verify REDIS_CLIENT is now visible
    cmd3 = "kubectl exec deployment/laravel-deployment -- php -r \"echo getenv('REDIS_CLIENT');\""
    stdin3, stdout3, stderr3 = client.exec_command(cmd3)
    print("REDIS_CLIENT in pod:", stdout3.read().decode('ascii', 'ignore'))
    
    client.close()

if __name__ == '__main__':
    main()
