<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$sources = \App\Post::whereIn('type', ['NEWS', 'RSS_NEWS'])->where('status', 'ACTIVE')->distinct()->pluck('source')->toArray();
print_r($sources);
