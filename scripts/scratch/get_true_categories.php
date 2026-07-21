<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== DISTINCT CATEGORY FIELD ===\n";
if (Schema::hasColumn('marketplace_listings', 'category')) {
    $dist = DB::table('marketplace_listings')->select('category')->distinct()->pluck('category');
    foreach($dist as $d) echo "- $d\n";
}

echo "\n=== DISTINCT TYPE FIELD ===\n";
if (Schema::hasColumn('marketplace_listings', 'type')) {
    $distType = DB::table('marketplace_listings')->select('type')->distinct()->pluck('type');
    foreach($distType as $d) echo "- $d\n";
}

echo "\n=== MARKETPLACE CATEGORIES TABLE ===\n";
if (Schema::hasTable('marketplace_categories')) {
    $cats = DB::table('marketplace_categories')->get();
    foreach($cats as $c) echo "- ID: {$c->id}, Name: {$c->name}, Label: {$c->label}\n";
}
