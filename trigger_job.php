<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$msg = \App\Models\WhatsappMessage::whereIn('id', [70, 71, 72, 73, 74, 75])->first();
if ($msg) {
    echo "Dispatching job for User {$msg->whatsapp_user_id} and Group {$msg->group_id}\n";
    \App\Jobs\ProcessWhatsappBatchJob::dispatch($msg->whatsapp_user_id, $msg->group_id);
    echo "Job dispatched!\n";
} else {
    echo "Messages not found.\n";
}
