<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$posts = App\Post::whereIn('type', ['SOCIAL_PIC', 'SOCIAL_VID', 'ROAD_INFO'])->get();
foreach ($posts as $p) {
    echo "ID: " . $p->id . " | user_id: " . $p->user_id . " | author_type: " . $p->author_type . " | type: " . $p->type . "\n";
}
