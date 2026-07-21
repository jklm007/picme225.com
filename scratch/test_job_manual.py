import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

# We'll just run a quick script that instantiates the job and runs handle() directly on a failed message.
cmd = """kubectl exec $(kubectl get pods -l app=laravel-worker -o jsonpath='{.items[0].metadata.name}') -- php -r "
require '/app/vendor/autoload.php';
\\$app = require '/app/bootstrap/app.php';
\\$app->make('Illuminate\\\\Contracts\\\\Console\\\\Kernel')->bootstrap();
\\$msg = \\App\\\\Models\\\\WhatsappMessage::where('status', 'failed')->orderBy('id', 'desc')->first();
if (\\$msg) {
    echo 'Testing message ' . \\$msg->id . ' for user ' . \\$msg->whatsapp_user_id . \\"\n\\";
    // reset status
    \\$msg->status = 'pending';
    \\$msg->batch_processed = 0;
    \\$msg->save();
    
    \\$job = new \\App\\\\Jobs\\\\ProcessWhatsappBatchJob(\\$msg->whatsapp_user_id, \\$msg->group_id);
    \\$job->handle();
    
    \\$msg->refresh();
    echo 'NEW STATUS: ' . \\$msg->status . \\"\n\\";
    echo 'NEW ERROR: ' . \\$msg->error_log . \\"\n\\";
}
" 2>&1"""
stdin, stdout, stderr = client.exec_command(cmd)
print(stdout.read().decode())
client.close()
