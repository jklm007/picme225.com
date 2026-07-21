<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Check in trash (soft deleted)
$inTrash = DB::table('marketplace_listings')
    ->whereNotNull('deleted_at')
    ->where('title', 'like', '%barbecue%')
    ->get(['id', 'title', 'deleted_at', 'cover_image']);

echo "=== In Trash (soft deleted) ===\n";
echo json_encode($inTrash, JSON_PRETTY_PRINT) . "\n";

// Check all including trashed
$all = DB::table('marketplace_listings')
    ->where('title', 'like', '%barbecue%')
    ->orWhere('title', 'like', '%Barbecue%')
    ->get(['id', 'title', 'deleted_at', 'cover_image']);

echo "\n=== All matching (active + trashed) ===\n";
echo json_encode($all, JSON_PRETTY_PRINT) . "\n";
