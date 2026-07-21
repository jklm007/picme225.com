<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// CHECK MARKETPLACE LISTINGS
$listings = \App\MarketplaceListing::latest()->limit(5)->get();
file_put_contents('test_listings.json', json_encode($listings));

// CHECK POSTS STORIES
$stories = \App\Post::latest()->limit(5)->get();
file_put_contents('test_all_stories.json', json_encode($stories));
