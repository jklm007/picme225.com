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
\$session = \$app->make('session');
\$driver = \$session->driver();
\$driver->start();
\$driver->put('foo', 'bar');
\$driver->save();
echo 'manual save done' . PHP_EOL;
"
"""
    stdin, stdout, stderr = client.exec_command(cmd)
    print(stdout.read().decode('ascii', 'ignore'))
    
    client.close()

if __name__ == '__main__':
    main()
