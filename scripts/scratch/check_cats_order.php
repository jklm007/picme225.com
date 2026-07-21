<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$cats = DB::table('marketplace_categories')->whereNull('parent_id')->orderBy('order_index')->get(['name', 'label', 'order_index']);
echo json_encode($cats);
