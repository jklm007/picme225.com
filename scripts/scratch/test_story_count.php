<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$count = \App\Post::where('type', 'SOCIAL_PIC')->count();
echo "COUNT OF SOCIAL_PIC: " . $count . "\n";
