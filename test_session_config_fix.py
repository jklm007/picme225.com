import paramiko
import time

def main():
    hostname = '109.199.123.69'
    username = 'root'
    password = 'Charlotte23'
    
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(hostname, username=username, password=password)
    
    # 1. Restore routes/web.php
    print("Restoring routes/web.php...")
    client.exec_command("kubectl exec deployment/laravel-deployment -- git checkout -- /app/routes/web.php")
    time.sleep(2)
    
    # 2. Dump config
    cmd = """kubectl exec deployment/laravel-deployment -- php -r "
require '/app/vendor/autoload.php';
\$app = require '/app/bootstrap/app.php';
\$app->make('config');
echo 'session.secure = ';
var_dump(config('session.secure'));
echo 'session.cookie = ';
var_dump(config('session.cookie'));
"
"""
    stdin, stdout, stderr = client.exec_command(cmd)
    print(stdout.read().decode('ascii', 'ignore'))
    
    client.close()

if __name__ == '__main__':
    main()
