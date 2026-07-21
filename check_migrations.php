<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
$migrations = DB::select("SELECT * FROM migrations ORDER BY id DESC LIMIT 20");
echo "RECENT MIGRATIONS IN DB:\n";
foreach ($migrations as $m) {
    echo $m->migration . "\n";
}
