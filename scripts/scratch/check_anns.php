<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$anns = \Illuminate\Support\Facades\DB::table('announcements')->get();
if ($anns->isEmpty()) {
    echo "La table announcements est totalement vide !\n";
} else {
    foreach($anns as $a) {
        echo "ID: " . $a->id . " - Title: " . $a->title . "\n";
    }
}
