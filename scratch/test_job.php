<?php
require '/app/vendor/autoload.php';
$app = require '/app/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$msg = \App\Models\WhatsappMessage::where('status', 'failed')->orderBy('id', 'desc')->first();
if ($msg) {
    echo "Testing message " . $msg->id . " user=" . $msg->whatsapp_user_id . "\n";
    $msg->status = 'pending';
    $msg->batch_processed = 0;
    $msg->save();
    $job = new \App\Jobs\ProcessWhatsappBatchJob($msg->whatsapp_user_id, $msg->group_id);
    $job->handle();
    $msg->refresh();
    echo "RESULT: " . $msg->status . "\n";
    if ($msg->error_log) { echo "ERROR: " . $msg->error_log . "\n"; }
} else {
    echo "No failed messages found.\n";
}
