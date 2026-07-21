<?php
require '/app/vendor/autoload.php';
$app = require '/app/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$deleted = DB::table('migrations')
    ->where('migration', '2026_06_26_163000_fix_postgres_check_constraints')
    ->delete();

echo "Deleted migration records: " . $deleted . "\n";
