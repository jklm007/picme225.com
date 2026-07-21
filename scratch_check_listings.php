<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\MarketplaceListing;

$listings = MarketplaceListing::orderBy('created_at', 'desc')->take(5)->get();
foreach ($listings as $l) {
    echo "ID: {$l->id}, Title: {$l->title}, Category: {$l->category}, Status: {$l->status}, Price: {$l->price}, Source: {$l->source}, AI Score: {$l->ai_confidence_score}%\n";
    echo "Metadata: " . json_encode($l->metadata) . "\n";
    echo "---------------------------\n";
}
