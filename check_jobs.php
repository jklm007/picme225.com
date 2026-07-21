<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- JOBS ---\n";
print_r(\Illuminate\Support\Facades\DB::table('jobs')->get()->toArray());

echo "\n--- FAILED JOBS ---\n";
print_r(\Illuminate\Support\Facades\DB::table('failed_jobs')->get()->toArray());
