<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$msg = \App\Models\WhatsappMessage::find(54);
if ($msg) {
    echo "Running job for user {$msg->whatsapp_user_id} and group_id {$msg->group_id}\n";
    $job = new \App\Jobs\ProcessWhatsappBatchJob($msg->whatsapp_user_id, $msg->group_id);
    $job->handle();
    echo "Job finished.\n";
} else {
    echo "Message 54 not found.\n";
}
