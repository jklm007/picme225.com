<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\WhatsappUser::where('name', 'Jack owen')->first();
if (!$user) {
    echo "User Jack owen not found\n";
    exit;
}

$messages = \App\Models\WhatsappMessage::where('whatsapp_user_id', $user->id)
    ->orderBy('id', 'desc')
    ->take(15)
    ->get(['id', 'content', 'batch_processed', 'status', 'created_at']);

foreach ($messages as $msg) {
    echo "ID: {$msg->id} | Status: {$msg->status} | Processed: {$msg->batch_processed} | Time: {$msg->created_at}\n";
    echo "Content: {$msg->content}\n";
    echo "----------------------------------------\n";
}
