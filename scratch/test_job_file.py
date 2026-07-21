import paramiko, time

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

# Write the test PHP to a file and run it
pod = "laravel-deployment-56f54497f-r8pmg"

test_php = '''<?php
require '/app/vendor/autoload.php';
$app = require '/app/bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();
$msg = \\App\\Models\\WhatsappMessage::where('status', 'failed')->orderBy('id', 'desc')->first();
if ($msg) {
    echo "Testing message " . $msg->id . " for user " . $msg->whatsapp_user_id . "\\n";
    $msg->status = 'pending';
    $msg->batch_processed = 0;
    $msg->save();
    $job = new \\App\\Jobs\\ProcessWhatsappBatchJob($msg->whatsapp_user_id, $msg->group_id);
    $job->handle();
    $msg->refresh();
    echo "STATUS: " . $msg->status . "\\n";
    echo "ERROR: " . $msg->error_log . "\\n";
} else {
    echo "No failed messages found.";
}
'''

# Write file locally
with open('scratch/test_job.php', 'w') as f:
    f.write(test_php)

# Copy to pod
sftp = client.open_sftp()
sftp.put('scratch/test_job.php', '/tmp/test_job.php')
sftp.close()

stdin, stdout, stderr = client.exec_command(f"kubectl cp /tmp/test_job.php {pod}:/tmp/test_job.php")
time.sleep(1)
stdin, stdout, stderr = client.exec_command(f"kubectl exec {pod} -- php /tmp/test_job.php 2>&1")
print(stdout.read().decode())
client.close()
