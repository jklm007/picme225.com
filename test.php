<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$listings = App\Models\MarketplaceListing::where('status', 'ACTIVE')
    ->whereNull('deleted_at')
    ->take(60)
    ->get();

try {
    $mapped = $listings->map(function($l) {
        return [
            'id'            => $l->id,
            'title'         => $l->title,
            'description'   => $l->description,
            'price'         => $l->price,
            'price_unit'    => $l->price_unit,
            'category'      => $l->category,
            'type'          => $l->type,
            'location_city' => $l->location_city,
            'media_url'     => $l->media_url,
            'status'        => $l->status,
        ];
    })->values();

    $json = json_encode($mapped);
    echo "JSON OUTPUT: \n" . $json . "\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
