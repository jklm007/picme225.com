<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$stops = \DB::table('pdp_stops')->where('name', 'like', '%Commissariat%')->get();
foreach($stops as $stop) {
    echo $stop->id . " | " . $stop->name . " | " . $stop->latitude . " | " . $stop->longitude . "\n";
}
