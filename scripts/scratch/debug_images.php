<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$news = App\Post::where('type', 'NEWS')->get();
echo "Total NEWS: " . $news->count() . "\n";
echo "NEWS with images: " . $news->whereNotNull('media_url')->count() . "\n";

foreach($news->whereNotNull('media_url')->take(5) as $n) {
    $parts = explode("\n\n", $n->content);
    echo "Title (extracted): " . ($parts[0] ?? 'Empty') . "\n";
    echo "Image: " . $n->media_url . "\n\n";
}
