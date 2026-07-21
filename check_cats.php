<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cats = \App\Models\MarketplaceCategory::select('id','name','label','icon')->orderBy('order_index')->get();
foreach ($cats as $c) {
    echo "ID:{$c->id} | name:{$c->name} | label:{$c->label} | icon:{$c->icon}\n";
}
