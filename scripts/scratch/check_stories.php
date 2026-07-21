<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$stories = App\Post::whereIn('type', ['SOCIAL_PIC', 'SOCIAL_VID', 'ROAD_INFO'])
    ->where('status', 'ACTIVE')
    ->get();

echo "Total stories found: " . $stories->count() . "\n";
foreach ($stories as $s) {
    echo "ID: " . $s->id . " | Type: " . $s->type . " | Status: " . $s->status . " | Expires: " . $s->expires_at . "\n";
}
