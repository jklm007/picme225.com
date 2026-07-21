<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$messages = \App\Models\WhatsappMessage::orderBy('id', 'desc')->take(20)->get();

foreach ($messages as $msg) {
    echo "MSG ID: {$msg->id} | User ID: {$msg->whatsapp_user_id} | Status: {$msg->status} | Processed: {$msg->batch_processed} | Time: {$msg->created_at}\n";
    echo "Content: " . substr(str_replace("\n", " ", $msg->content), 0, 100) . "\n";
    echo "----------------------------------------\n";
}

echo "\n--- LISTINGS ---\n";
$listings = \App\Models\MarketplaceListing::orderBy('id', 'desc')->take(5)->get();
foreach ($listings as $listing) {
    echo "LST ID: {$listing->id} | User ID: {$listing->user_id} | Source: {$listing->source} | MsgID: {$listing->whatsapp_message_id}\n";
    echo "Title: {$listing->title}\n";
}
