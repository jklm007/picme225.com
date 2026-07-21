import paramiko

def main():
    hostname = '109.199.123.69'
    username = 'root'
    password = 'Charlotte23'
    
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(hostname, username=username, password=password)
    
    commands = """
    kubectl exec deployment/laravel-deployment -- php -r "
    echo 'predis: ';
    echo class_exists('Predis\\\\Client') ? 'YES' : 'NO';
    echo PHP_EOL;
    
    echo 'phpredis ext: ';
    echo extension_loaded('redis') ? 'YES' : 'NO';
    echo PHP_EOL;
    
    echo 'REDIS_CLIENT env: ';
    echo getenv('REDIS_CLIENT') ?: 'not set';
    echo PHP_EOL;
    
    echo 'REDIS_HOST env: ';
    echo getenv('REDIS_HOST') ?: 'not set';
    echo PHP_EOL;
    
    echo 'SESSION_DRIVER env: ';
    echo getenv('SESSION_DRIVER') ?: 'not set';
    echo PHP_EOL;
    "
    """
    
    stdin, stdout, stderr = client.exec_command(commands)
    print("Stdout:", stdout.read().decode('ascii', 'ignore'))
    print("Stderr:", stderr.read().decode('ascii', 'ignore'))
    client.close()

if __name__ == '__main__':
    main()
