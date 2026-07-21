import paramiko

def main():
    hostname = '109.199.123.69'
    username = 'root'
    password = 'Charlotte23'
    
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(hostname, username=username, password=password)
    
    # 1. Modify routes/web.php directly in the pod to add a test route
    add_route = r"""kubectl exec deployment/laravel-deployment -- bash -c "echo \"\nRoute::get('/test-session', function () { session(['test_key' => 'hello']); return response()->json(['session_id' => session()->getId(), 'has_cookie' => Cookie::has('laravel_session')]); });\" >> /app/routes/web.php" """
    client.exec_command(add_route)
    
    # 2. Hit the test route via curl (which goes through Traefik -> Nginx -> PHP)
    print("Hitting via public URL...")
    cmd_curl = "curl -s -D - -H 'Accept: application/json' 'https://picme225.site/test-session'"
    stdin, stdout, stderr = client.exec_command(cmd_curl)
    print(stdout.read().decode('ascii', 'ignore'))
    
    # 3. Hit via pod IP locally
    pod_ip_cmd = "kubectl get pod -l app=laravel -o jsonpath='{.items[0].status.podIP}'"
    stdin2, stdout2, stderr2 = client.exec_command(pod_ip_cmd)
    pod_ip = stdout2.read().decode('ascii', 'ignore').strip().strip("'")
    
    print(f"\nHitting via pod IP: {pod_ip}...")
    cmd_curl2 = f"curl -s -D - -H 'Accept: application/json' -H 'Host: picme225.site' 'http://{pod_ip}/test-session'"
    stdin3, stdout3, stderr3 = client.exec_command(cmd_curl2)
    print(stdout3.read().decode('ascii', 'ignore'))
    
    client.close()

if __name__ == '__main__':
    main()
