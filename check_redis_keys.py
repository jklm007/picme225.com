import paramiko

def main():
    hostname = '109.199.123.69'
    username = 'root'
    password = 'Charlotte23'
    
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(hostname, username=username, password=password)
    
    cmd = """kubectl exec deployment/laravel-deployment -- php -r "
require '/app/vendor/autoload.php';
\$app = require '/app/bootstrap/app.php';
\$kernel = \$app->make(Illuminate\Contracts\Console\Kernel::class);
\$kernel->bootstrap();
\$redis = Illuminate\Support\Facades\Redis::connection();
\$keys = \$redis->keys('*laravel_session*');
echo 'Session keys in Redis: ' . count(\$keys) . PHP_EOL;
// let's see if hitting the page creates one
file_get_contents('http://localhost/admin/login');
\$keys2 = \$redis->keys('*laravel_session*');
echo 'Session keys after hit: ' . count(\$keys2) . PHP_EOL;
"
"""
    stdin, stdout, stderr = client.exec_command(cmd)
    print(stdout.read().decode('ascii', 'ignore'))
    
    client.close()

if __name__ == '__main__':
    main()
