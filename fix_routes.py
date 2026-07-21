import paramiko
import time

def main():
    hostname = '109.199.123.69'
    username = 'root'
    password = 'Charlotte23'
    
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(hostname, username=username, password=password)
    
    # Remove the last line (the bad nRoute)
    print("Fixing routes/web.php...")
    client.exec_command("kubectl exec deployment/laravel-deployment -- sed -i '$d' /app/routes/web.php")
    time.sleep(1)
    
    # Hit via public URL to verify it's up
    cmd_curl = "curl -s -o /dev/null -w '%{http_code}' 'https://picme225.site/admin/login'"
    stdin, stdout, stderr = client.exec_command(cmd_curl)
    print("HTTP Code:", stdout.read().decode('ascii', 'ignore').strip())
    
    client.close()

if __name__ == '__main__':
    main()
