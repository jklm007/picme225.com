import paramiko

def main():
    hostname = '109.199.123.69'
    username = 'root'
    password = 'Charlotte23'
    
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(hostname, username=username, password=password)
    
    # Test session bootstrap through Laravel's kernel
    cmd = r"""kubectl exec deployment/laravel-deployment -- php -r "
define('LARAVEL_START', microtime(true));
require '/app/vendor/autoload.php';
\$app = require '/app/bootstrap/app.php';
\$kernel = \$app->make(\Illuminate\Contracts\Http\Kernel::class);
\$req = \Illuminate\Http\Request::create('https://picme225.site/admin/login', 'GET');
\$req->headers->set('X-Forwarded-Proto', 'https');
\$resp = \$kernel->handle(\$req);
echo 'Status: ' . \$resp->getStatusCode() . PHP_EOL;
echo 'Set-Cookie: ';
foreach (\$resp->headers->getCookies() as \$c) {
    echo \$c->getName() . '=' . substr(\$c->getValue(), 0, 20) . '... ';
}
echo PHP_EOL;
"
"""
    
    stdin, stdout, stderr = client.exec_command(cmd, timeout=30)
    out = stdout.read().decode('ascii', 'ignore')
    err = stderr.read().decode('ascii', 'ignore')
    print("Out:", out)
    if err:
        print("Err:", err[-1000:])
    client.close()

if __name__ == '__main__':
    main()
