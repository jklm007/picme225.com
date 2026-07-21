<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Post;

$count = Post::where('status', 'ACTIVE')->count();
echo "Total ACTIVE posts: " . $count . "\n";

$byType = Post::where('status', 'ACTIVE')
    ->groupBy('type')
    ->selectRaw('type, count(*) as cnt')
    ->pluck('cnt', 'type')
    ->toArray();

echo "By type: " . json_encode($byType) . "\n";

$expired = Post::where('status', 'ACTIVE')
    ->where('expires_at', '<', now())
    ->whereNotNull('expires_at')
    ->count();
echo "Expired posts (hidden): " . $expired . "\n";

$recent = Post::where('status', 'ACTIVE')
    ->whereNull('deleted_at')
    ->where(function($q) {
        $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
    })
    ->count();
echo "Visible posts (no expire): " . $recent . "\n";
