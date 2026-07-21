<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$stories = \App\Post::with('user:id,first_name,last_name,picture')
    ->whereIn('type', ['SOCIAL_PIC', 'SOCIAL_VID'])
    ->where('status', 'ACTIVE')
    ->where('expires_at', '>', now())
    ->latest()
    ->limit(20)
    ->get()->map(function($post) {
        if ($post->media_url && !str_starts_with($post->media_url, 'http')) {
            $post->media_url = asset('storage/' . $post->media_url);
        }
        return $post;
    })->values()->all(); // FORCE AS NORMAL ARRAY

file_put_contents('stories.json', json_encode(['stories' => $stories]));
