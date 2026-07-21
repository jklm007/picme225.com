<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Simulate the getStories logic
$stories = App\Post::whereIn('type', ['SOCIAL_PIC', 'SOCIAL_VID', 'ROAD_INFO'])
    ->where('status', 'ACTIVE')
    ->where('expires_at', '>', now())
    ->latest()
    ->limit(20)
    ->get();

$stories = $stories->map(function($post) {
    $post->author_name = "Test Name";
    $post->author_picture = "Test Pic";
    return $post;
});

echo "JSON output for first story:\n";
echo json_encode($stories->first(), JSON_PRETTY_PRINT);
