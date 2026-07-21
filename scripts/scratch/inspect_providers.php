<?php
define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- PROVIDERS SCHEMA ---\n";
$columns = DB::select('DESCRIBE providers');
foreach ($columns as $col) {
    echo $col->Field . " (" . $col->Type . ")\n";
}

echo "\n--- DRIVER (PROVIDER) DETAILS ---\n";
$p = DB::table('providers')->where('email', 'demo@demo.com')->first();
if ($p) {
    $data = (array)$p;
    foreach ($data as $key => $val) {
        if (!in_array($key, ['password', 'remember_token'])) {
           echo "$key: $val\n";
        }
    }
} else {
    echo "Driver demo@demo.com NOT found.\n";
}
