<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$to_skip = [
    '2026_07_09_082324_add_performance_indexes_to_critical_tables',
];

foreach ($to_skip as $migration) {
    $already = DB::table('migrations')->where('migration', $migration)->count();
    if ($already === 0) {
        DB::table('migrations')->insert(['migration' => $migration, 'batch' => 2]);
        echo "Marked as done: $migration\n";
    } else {
        echo "Already marked: $migration\n";
    }
}
echo "All done.\n";
