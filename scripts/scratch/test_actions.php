<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$listings = \App\MarketplaceListing::take(5)->get();
foreach($listings as $l) {
    echo "Title: {$l->title} | Category: {$l->category}\n";
    echo "Actions: " . json_encode($l->available_actions) . "\n\n";
}
