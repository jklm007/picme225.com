import paramiko

def main():
    hostname = '109.199.123.69'
    username = 'root'
    password = 'Charlotte23'
    
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(hostname, username=username, password=password)
    
    # Check current SESSION_SECURE_COOKIE setting
    cmd = """kubectl exec deployment/laravel-deployment -- php -r "
echo 'SESSION_SECURE_COOKIE: ';
echo getenv('SESSION_SECURE_COOKIE') !== false ? getenv('SESSION_SECURE_COOKIE') : '(not set)';
echo PHP_EOL;
echo 'config session secure: ';
define('LARAVEL_START', microtime(true));
require '/app/vendor/autoload.php';
\$app = require '/app/bootstrap/app.php';
\$app->make('config');
echo var_export(config('session.secure'), true);
echo PHP_EOL;
"
"""
    stdin, stdout, stderr = client.exec_command(cmd)
    print(stdout.read().decode('ascii', 'ignore'))
    
    client.close()

if __name__ == '__main__':
    main()
