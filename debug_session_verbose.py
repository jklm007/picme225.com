import paramiko

def main():
    hostname = '109.199.123.69'
    username = 'root'
    password = 'Charlotte23'
    
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(hostname, username=username, password=password)
    
    # Check the Setting facade - does it crash on boot?
    cmd = r"""kubectl exec deployment/laravel-deployment -- php -r "
define('LARAVEL_START', microtime(true));
require '/app/vendor/autoload.php';
\$app = require '/app/bootstrap/app.php';
\$kernel = \$app->make(\Illuminate\Contracts\Http\Kernel::class);

// Simulate exact HTTP GET to /admin/login with no Host header override issues
\$req = \Illuminate\Http\Request::create('http://picme225.site/admin/login', 'GET');
\$req->headers->set('X-Forwarded-Proto', 'https');
\$req->headers->set('X-Forwarded-Host', 'picme225.site');

try {
    \$resp = \$kernel->handle(\$req);
    echo 'Status: ' . \$resp->getStatusCode() . PHP_EOL;
    \$cookies = \$resp->headers->getCookies();
    echo 'Cookies count: ' . count(\$cookies) . PHP_EOL;
    foreach (\$cookies as \$c) {
        echo '  Cookie: ' . \$c->getName() . PHP_EOL;
    }
} catch (\Exception \$e) {
    echo 'ERROR: ' . \$e->getMessage() . PHP_EOL;
    echo \$e->getTraceAsString() . PHP_EOL;
}
" 2>&1
"""
    stdin, stdout, stderr = client.exec_command(cmd, timeout=30)
    print(stdout.read().decode('ascii', 'ignore'))
    client.close()

if __name__ == '__main__':
    main()
