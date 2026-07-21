<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$listings = \App\MarketplaceListing::all();
foreach($listings as $l) {
    echo "ID: {$l->id} | Title: {$l->title} | Category: {$l->category}\n";
}
