<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$images = [
    'VEHICLES' => 'https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?auto=format&fit=crop&w=800&q=80',
    'ARTICLE' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=800&q=80',
    'REAL_ESTATE' => 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?auto=format&fit=crop&w=800&q=80',
    'TICKETS' => 'https://images.unsplash.com/photo-1540039155732-684735035727?auto=format&fit=crop&w=800&q=80',
    'SERVICES' => 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=800&q=80'
];

$listings = \App\Models\MarketplaceListing::all();
$count = 0;
foreach($listings as $l) {
    $cat = \App\Models\MarketplaceCategory::where('name', $l->category)->first();
    $mainCatName = 'ARTICLE'; // default
    if ($cat) {
        if ($cat->parent_id == null) {
            $mainCatName = $cat->name;
        } else {
            $parent = \App\Models\MarketplaceCategory::find($cat->parent_id);
            if ($parent) $mainCatName = $parent->name;
        }
    }
    
    $url = $images[$mainCatName] ?? $images['ARTICLE'];
    
    \Illuminate\Support\Facades\DB::table((new \App\Models\MarketplaceListing)->getTable())->where('id', $l->id)->update(['cover_image' => $url]);
    $count++;
}
echo "Added images to $count listings.\n";
