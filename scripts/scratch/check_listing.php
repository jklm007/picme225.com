<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$listingExists = \Illuminate\Support\Facades\DB::table('marketplace_listings')->where('id', 2)->exists();
echo "Listing 2 exists in marketplace_listings: " . ($listingExists ? "YES" : "NO") . "\n";
