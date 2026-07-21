<?php
require '/app/vendor/autoload.php';
$app = require_once '/app/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $results = DB::select("SELECT conname, pg_get_constraintdef(oid) as def FROM pg_constraint WHERE conrelid = 'posts'::regclass AND contype='c'");
    foreach ($results as $row) {
        echo $row->conname . ": " . $row->def . "\n";
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
