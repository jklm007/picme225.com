<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    $res = DB::select('DESCRIBE post_comments');
    foreach($res as $col) {
        echo $col->Field . " - " . $col->Type . "\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
