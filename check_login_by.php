<?php
require '/app/vendor/autoload.php';
$app = require '/app/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Get the CHECK constraint definition for login_by
$constraints = DB::select("
    SELECT pg_get_constraintdef(c.oid) AS constraint_def
    FROM pg_constraint c
    JOIN pg_class t ON c.conrelid = t.oid
    WHERE t.relname = 'providers'
      AND c.conname = 'providers_login_by_check'
");

echo "=== login_by CHECK constraint ===\n";
foreach ($constraints as $c) {
    echo $c->constraint_def . "\n";
}

// Also check existing providers to see what login_by values are used
$existing = DB::select("SELECT DISTINCT login_by FROM providers LIMIT 10");
echo "\n=== Existing login_by values ===\n";
foreach ($existing as $r) {
    echo "  " . $r->login_by . "\n";
}
