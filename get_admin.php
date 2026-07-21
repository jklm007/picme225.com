<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $admins = DB::table('admins')->get();
    echo "Found " . count($admins) . " admins\n";
    foreach($admins as $a) {
        echo "Email: " . $a->email . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
