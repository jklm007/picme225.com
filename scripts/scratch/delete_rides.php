<?php

define('LARAVEL_START', microtime(true));
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $deleted = DB::table('user_requests')
        ->whereNotIn('status', ['COMPLETED', 'CANCELLED'])
        ->delete();
    echo "SUCCESS: Deleted $deleted ongoing rides.\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
