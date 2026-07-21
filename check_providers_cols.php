<?php
require '/app/vendor/autoload.php';
$app = require '/app/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Check providers table NOT NULL columns without defaults
$cols = DB::select("
    SELECT column_name, data_type, column_default, is_nullable 
    FROM information_schema.columns 
    WHERE table_name = 'providers' 
      AND is_nullable = 'NO' 
      AND column_default IS NULL
    ORDER BY ordinal_position
");

echo "=== NOT NULL columns in providers (no default) ===\n";
foreach ($cols as $c) {
    echo "  {$c->column_name} | {$c->data_type}\n";
}
