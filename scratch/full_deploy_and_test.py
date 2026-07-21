import paramiko, tarfile, time

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

# Step 1: Deploy the fixed PHP job
print("=== STEP 1: Deploy fixed ProcessWhatsappBatchJob ===")
with tarfile.open('fixes_final.tar.gz', 'w:gz') as tar:
    tar.add('app/Jobs/ProcessWhatsappBatchJob.php')
    tar.add('app/Http/Controllers/Resource/AdCampaignResource.php')
    tar.add('resources/views/admin/ad-campaign/edit.blade.php')
    tar.add('resources/views/admin/ad-campaign/show.blade.php')

sftp = client.open_sftp()
sftp.put('fixes_final.tar.gz', '/tmp/fixes_final.tar.gz')
sftp.close()

for label in ['app=laravel', 'app=laravel-worker']:
    stdin, stdout, stderr = client.exec_command(f"kubectl get pods -l {label} -o jsonpath='{{.items[*].metadata.name}}'")
    pods = stdout.read().decode().strip().strip("'").split()
    for pod in pods:
        if not pod: continue
        print(f"Deploying to {pod}...")
        client.exec_command(f"kubectl cp /tmp/fixes_final.tar.gz {pod}:/tmp/fixes_final.tar.gz")
        time.sleep(1)
        client.exec_command(f"kubectl exec {pod} -- tar -xzf /tmp/fixes_final.tar.gz -C /app")
        time.sleep(1)
        if 'worker' in label:
            i, o, e = client.exec_command(f"kubectl exec {pod} -- php artisan queue:restart")
            print("Worker restart:", o.read().decode())

# Step 2: Clear all Groq-related cache entries + full cache clear
print("\n=== STEP 2: Clear Groq cache entries ===")
pod = "laravel-deployment-56f54497f-r8pmg"
cache_cmd = """kubectl exec {} -- php -r "
require '/app/vendor/autoload.php';
\\$app = require '/app/bootstrap/app.php';
\\$app->make('Illuminate\\\\Contracts\\\\Console\\\\Kernel')->bootstrap();
Illuminate\\\\Support\\\\Facades\\\\Cache::forget('groq_available_vision_models');
Illuminate\\\\Support\\\\Facades\\\\Cache::forget('groq_unavailable_models');
Illuminate\\\\Support\\\\Facades\\\\Cache::forget('groq_last_working_model');
echo 'Groq cache cleared!';
" 2>&1""".format(pod)
stdin, stdout, stderr = client.exec_command(cache_cmd)
print(stdout.read().decode())

print("\n=== STEP 3: Full artisan cache:clear ===")
i, o, e = client.exec_command(f"kubectl exec {pod} -- php artisan cache:clear")
print(o.read().decode())

# Step 4: Test Groq NOW (rerun message 2501)
print("\n=== STEP 4: Test message re-processing ===")
test_cmd = """kubectl exec {} -- php -r "
require '/app/vendor/autoload.php';
\\$app = require '/app/bootstrap/app.php';
\\$app->make('Illuminate\\\\Contracts\\\\Console\\\\Kernel')->bootstrap();
\\$msg = \\App\\\\Models\\\\WhatsappMessage::where('status', 'failed')->orderBy('id', 'desc')->first();
if (\\$msg) {{
    echo 'Testing message ' . \\$msg->id . \" for user \" . \\$msg->whatsapp_user_id . \"\\n\";
    \\$msg->status = 'pending';
    \\$msg->batch_processed = 0;
    \\$msg->save();
    \\$job = new \\App\\\\Jobs\\\\ProcessWhatsappBatchJob(\\$msg->whatsapp_user_id, \\$msg->group_id);
    \\$job->handle();
    \\$msg->refresh();
    echo 'STATUS: ' . \\$msg->status . \"\\n\";
    echo 'ERROR: ' . \\$msg->error_log . \"\\n\";
}} else {{
    echo 'No failed messages found.';
}}
" 2>&1""".format(pod)
stdin, stdout, stderr = client.exec_command(test_cmd)
output = stdout.read().decode()
print(output)

client.close()
print("\n=== DONE ===")
