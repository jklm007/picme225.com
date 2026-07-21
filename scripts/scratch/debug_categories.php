<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "=== CATEGORIES ===\n";
$cats = \App\Models\MarketplaceCategory::all(['id','name','label','parent_id']);
echo json_encode($cats->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

echo "=== LISTINGS (category column values) ===\n";
$listings = \DB::table('marketplace_listings')->select('id','title','category','status')->whereNull('deleted_at')->get();
echo json_encode($listings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

echo "=== COUNT per category ===\n";
$counts = \DB::table('marketplace_listings')->whereNull('deleted_at')->where('status','ACTIVE')->groupBy('category')->selectRaw('category, count(*) as cnt')->get();
echo json_encode($counts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
