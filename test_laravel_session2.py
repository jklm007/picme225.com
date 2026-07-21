import paramiko
import time

def main():
    hostname = '109.199.123.69'
    username = 'root'
    password = 'Charlotte23'
    
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(hostname, username=username, password=password)
    
    # 1. Clear route cache
    print("Clearing route cache...")
    client.exec_command("kubectl exec deployment/laravel-deployment -- php artisan route:clear")
    time.sleep(2)
    
    # 2. Hit via public URL
    print("Hitting via public URL...")
    cmd_curl = "curl -s -D - -H 'Accept: application/json' 'https://picme225.site/test-session'"
    stdin, stdout, stderr = client.exec_command(cmd_curl)
    print(stdout.read().decode('ascii', 'ignore'))
    
    # Cleanup
    client.exec_command("kubectl exec deployment/laravel-deployment -- git checkout -- routes/web.php")
    
    client.close()

if __name__ == '__main__':
    main()
