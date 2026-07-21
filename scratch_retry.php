<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\WhatsappMessage;
use App\Jobs\ProcessWhatsappMessageJob;

$msg = WhatsappMessage::find(12);
if ($msg) {
    echo "Retrying message ID: {$msg->id}, Content: {$msg->content}\n";
    $msg->update(['status' => 'pending']);
    ProcessWhatsappMessageJob::dispatch($msg);
    echo "Dispatched!\n";
} else {
    echo "No failed messages found.\n";
}
