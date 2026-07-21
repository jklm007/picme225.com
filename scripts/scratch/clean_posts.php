<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

\Illuminate\Support\Facades\DB::table('posts')->whereIn('type', ['NEWS', 'RSS_NEWS', 'ROAD_INFO'])->delete();
\Illuminate\Support\Facades\Cache::flush();
echo "Posts deleted and cache flushed.\n";
