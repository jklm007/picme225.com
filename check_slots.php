<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach(App\Models\AdSlot::all() as $s) {
    echo $s->name . ' - AdMob: ' . $s->admob_unit_id . "\n";
}
