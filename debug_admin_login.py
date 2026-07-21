import paramiko

def main():
    hostname = '109.199.123.69'
    username = 'root'
    password = 'Charlotte23'
    
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(hostname, username=username, password=password)
    
    # Test via actual HTTP through Nginx (not bypassing it via PHP directly)
    # First get the pod IP
    cmd_ip = "kubectl get pod -l app=laravel -o jsonpath='{.items[0].status.podIP}'"
    stdin, stdout, stderr = client.exec_command(cmd_ip)
    pod_ip = stdout.read().decode('ascii', 'ignore').strip().strip("'")
    print("Pod IP:", pod_ip)
    
    # Hit via curl with -v to see ALL headers including cookies
    cmd = f"""curl -s -D - -H 'Host: picme225.site' http://{pod_ip}/admin/login 2>/dev/null | head -20"""
    stdin2, stdout2, stderr2 = client.exec_command(cmd)
    print("=== Response headers ===")
    print(stdout2.read().decode('ascii', 'ignore'))
    
    # Also check what the route middleware stack looks like
    cmd3 = r"""kubectl exec deployment/laravel-deployment -- php -r "
define('LARAVEL_START', microtime(true));
require '/app/vendor/autoload.php';
\$app = require '/app/bootstrap/app.php';
\$router = \$app->make('router');
\$routes = \$router->getRoutes();
foreach (\$routes as \$route) {
    if (str_contains(\$route->uri(), 'admin/login')) {
        echo 'URI: ' . \$route->uri() . PHP_EOL;
        echo 'Middleware: ' . implode(', ', \$route->middleware()) . PHP_EOL;
    }
}
"
"""
    stdin3, stdout3, stderr3 = client.exec_command(cmd3)
    print("=== /admin/login middleware ===")
    print(stdout3.read().decode('ascii', 'ignore'))
    
    client.close()

if __name__ == '__main__':
    main()
