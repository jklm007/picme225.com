<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$listing = \App\Models\MarketplaceListing::orderBy('id', 'desc')->first();
if ($listing) {
    echo "=== Latest Listing ===\n";
    echo "Title: " . $listing->title . "\n";
    echo "Price: " . $listing->price . "\n";
    echo "Status: " . $listing->status . "\n";
    echo "Content: " . $listing->description . "\n";
    echo "AI Score: " . $listing->ai_confidence_score . "\n";
} else {
    echo "No listings found.\n";
}

$message = \App\Models\WhatsappMessage::orderBy('id', 'desc')->first();
if ($message) {
    echo "\n=== Latest Message ===\n";
    echo "Content: " . $message->content . "\n";
    echo "Status: " . $message->status . "\n";
    echo "Error Log: " . $message->error_log . "\n";
} else {
    echo "No messages found.\n";
}
