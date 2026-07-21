<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Check if table exists
$tableExists = \Illuminate\Support\Facades\Schema::hasTable('whatsapp_messages');
echo "Table whatsapp_messages exists: " . ($tableExists ? 'YES' : 'NO') . "\n";

if ($tableExists) {
    $msg = \Illuminate\Support\Facades\DB::table('whatsapp_messages')->orderBy('id', 'desc')->first();
    if ($msg) {
        echo "\n=== Latest WhatsApp Message ===\n";
        echo "ID: " . $msg->id . "\n";
        echo "Content: " . $msg->content . "\n";
        echo "Status: " . $msg->status . "\n";
        echo "Error: " . ($msg->error_log ?? 'none') . "\n";
        echo "Created: " . $msg->created_at . "\n";
    } else {
        echo "No messages in DB yet.\n";
        echo ">>> The webhook may not have received any message. Check GROQ_API_KEY env var below:\n";
        echo "GROQ_API_KEY: " . (getenv('GROQ_API_KEY') ? 'SET ('.strlen(getenv('GROQ_API_KEY')).' chars)' : 'NOT SET') . "\n";
    }
}

// Check latest listing from whatsapp source
$listing = \Illuminate\Support\Facades\DB::table('marketplace_listings')
    ->where('source', 'whatsapp')
    ->orderBy('id', 'desc')
    ->first();

if ($listing) {
    echo "\n=== Latest WhatsApp Listing ===\n";
    echo "Title: " . $listing->title . "\n";
    echo "Price: " . $listing->price . "\n";
    echo "Status: " . $listing->status . "\n";
    echo "AI Score: " . ($listing->ai_confidence_score ?? 'N/A') . "\n";
    echo "Created: " . $listing->created_at . "\n";
} else {
    echo "\nNo whatsapp-sourced listings found.\n";
}

// Check failed jobs
$failed = \Illuminate\Support\Facades\DB::table('failed_jobs')->orderBy('id', 'desc')->take(3)->get();
echo "\n=== Failed Jobs (" . count($failed) . ") ===\n";
foreach ($failed as $job) {
    echo "- " . $job->payload . "\n  Error: " . substr($job->exception, 0, 200) . "\n";
}
