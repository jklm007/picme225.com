<?php
require '/app/vendor/autoload.php';
$app = require '/app/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$fkeys = DB::select("
    SELECT
        tc.table_name AS foreign_table,
        tc.constraint_name,
        kcu.column_name AS foreign_column
    FROM
        information_schema.table_constraints AS tc
        JOIN information_schema.key_column_usage AS kcu
          ON tc.constraint_name = kcu.constraint_name
          AND tc.table_schema = kcu.table_schema
        JOIN information_schema.constraint_column_usage AS ccu
          ON ccu.constraint_name = tc.constraint_name
    WHERE tc.constraint_type = 'FOREIGN KEY'
      AND ccu.table_name = 'pdp_stops'
");

echo "=== FOREIGN KEYS REFERENCING pdp_stops ===\n";
foreach ($fkeys as $fk) {
    echo "  Table: {$fk->foreign_table} | Constraint: {$fk->constraint_name} | Column: {$fk->foreign_column}\n";
}
echo "\n";
