<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$stops = \DB::table('pdp_stops')->get();
foreach($stops as $stop) {
    if ($stop->id >= 350) { // Assuming recent stops are >= 350 based on the IDs seen
        echo $stop->id . " | " . $stop->name . " | " . $stop->latitude . " | " . $stop->longitude . "\n";
    }
}
