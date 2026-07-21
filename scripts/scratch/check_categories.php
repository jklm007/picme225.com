<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$listings = \App\MarketplaceListing::select('id', 'title', 'category', 'type')->get();
foreach($listings as $l) {
    echo $l->id . " | " . $l->title . " | " . $l->category . " | " . $l->type . "\n";
}
