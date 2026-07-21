<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$msgs = \App\Models\WhatsappMessage::orderBy('id', 'desc')->take(1)->get();
echo "--- LATEST WHATSAPP MESSAGE ---\n";
foreach($msgs as $msg) {
    echo "ID: {$msg->id}\nContent: {$msg->content}\nStatus: {$msg->status}\nError: {$msg->error_log}\nMediaCount: " . count($msg->medias??[]) . "\n";
}

echo "\n--- LATEST MARKETPLACE LISTING ---\n";
$listings = \App\Models\MarketplaceListing::orderBy('id', 'desc')->take(1)->get();
foreach($listings as $l) {
    echo "ID: {$l->id}\nTitle: {$l->title}\nType: {$l->type}\nSource: {$l->source}\nCoverImage: {$l->cover_image}\nImagesCount: " . count($l->images??[]) . "\n";
}
