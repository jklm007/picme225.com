import paramiko
import json

def run_ssh_command(host, user, password, command):
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    try:
        client.connect(host, username=user, password=password, timeout=10)
        stdin, stdout, stderr = client.exec_command(command)
        out = stdout.read().decode()
        err = stderr.read().decode()
        return out, err
    except Exception as e:
        return str(e), ""
    finally:
        client.close()

if __name__ == "__main__":
    # 1. Fetch the ReplicaSet JSON
    cmd_get_rs = "k3s kubectl get rs laravel-deployment-ff9cc75cd -o json"
    out, err = run_ssh_command('109.199.123.69', 'root', 'Charlotte23', cmd_get_rs)
    if not out:
        print("Failed to get RS:", err)
        exit(1)
        
    rs_data = json.loads(out)
    pod_template = rs_data['spec']['template']
    
    # 2. Modify it to be a standalone Pod named 'laravel-test-ff9cc75cd'
    pod_manifest = {
        "apiVersion": "v1",
        "kind": "Pod",
        "metadata": {
            "name": "laravel-test-ff9cc75cd",
            "namespace": "default",
            "labels": pod_template["metadata"]["labels"]
        },
        "spec": pod_template["spec"]
    }
    
    # Remove any node affinity or tolerations that might prevent it from scheduling easily
    # But for a test pod, it's fine. We'll change the restart policy.
    pod_manifest["spec"]["restartPolicy"] = "Never"
    
    # Let's override the command so it doesn't try to run nginx/php-fpm and clash with ports if hostNetwork is used,
    # or just let it run normally as a pod. Laravel deployment doesn't usually use host ports.
    
    pod_json_str = json.dumps(pod_manifest)
    
    # 3. Apply the pod
    cmd_apply = f"cat << 'EOF' > /tmp/test-pod-ff9cc75cd.json\n{pod_json_str}\nEOF\nk3s kubectl apply -f /tmp/test-pod-ff9cc75cd.json"
    out_apply, err_apply = run_ssh_command('109.199.123.69', 'root', 'Charlotte23', cmd_apply)
    print("APPLY:", out_apply, err_apply)
    
    # 4. Wait for pod and check contents
    cmd_wait = "k3s kubectl wait --for=condition=Ready pod/laravel-test-ff9cc75cd --timeout=30s && k3s kubectl exec laravel-test-ff9cc75cd -- ls -la /app/resources/views/user/marketplace/ 2>/dev/null || echo 'Not found'"
    out_wait, err_wait = run_ssh_command('109.199.123.69', 'root', 'Charlotte23', cmd_wait)
    print("WAIT AND EXEC:", out_wait, err_wait)
