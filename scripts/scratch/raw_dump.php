<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$types = DB::table('service_types')->get();
foreach ($types as $t) {
    echo "ID: {$t->id} | Name: {$t->name} | Variants: {$t->allowed_variants}\n";
}
