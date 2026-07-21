<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $listingsCount = DB::table('marketplace_listings')->count();
    echo "--- TOTAL MARKETPLACE LISTINGS: $listingsCount ---\n\n";

    if ($listingsCount > 0) {
        $byCategory = DB::table('marketplace_listings')
            ->select('category', DB::raw('count(*) as total'))
            ->groupBy('category')
            ->get();
        echo "Listings by Category:\n";
        foreach ($byCategory as $bc) {
            echo "  - {$bc->category}: {$bc->total}\n";
        }
        echo "\n";
        
        $listings = DB::table('marketplace_listings')->get();
        echo "Listing Details:\n";
        foreach ($listings as $l) {
            $title = isset($l->title) ? $l->title : 'No Title';
            $category = isset($l->category) ? $l->category : 'No Cat';
            $price = isset($l->price) ? $l->price : '0';
            $type = isset($l->type) ? $l->type : 'No Type';
            echo "ID: {$l->id} | Title: {$title} | Type: {$type} | Cat: {$category} | Price: {$price}\n";
        }
    } else {
        echo "No marketplace listings found.\n";
    }

    echo "\n--------------------------------------------------------\n";
    $bookingsCount = DB::table('rental_bookings')->count();
    echo "--- TOTAL RENTAL BOOKINGS: $bookingsCount ---\n";
    if ($bookingsCount > 0) {
        $bookings = DB::table('rental_bookings')->get();
        foreach ($bookings as $b) {
            echo "ID: {$b->id} | Listing ID: {$b->listing_id} | Total Price: {$b->total_price} | Status: {$b->status}\n";
        }
    } else {
        echo "No rental bookings found.\n";
    }

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
