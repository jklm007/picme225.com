<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$count = \App\Post::where('type', 'NEWS')->count();
echo "Total NEWS posts: " . $count . "\n";

$latest = \App\Post::where('type', 'NEWS')->latest()->first();
if ($latest) {
    echo "Latest post title: " . $latest->content . "\n";
    echo "Status: " . $latest->status . "\n";
    echo "Expires: " . ($latest->expires_at ? $latest->expires_at->toDateTimeString() : 'NULL') . "\n";
}
