#!/bin/bash
POD=$(sudo k3s kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}')
echo "=== Pod: $POD ==="

echo "--- Dropping the bad calculator CHECK constraint directly ---"
sudo k3s kubectl exec "$POD" -- php -r "
require '/app/vendor/autoload.php';
\$app = require '/app/bootstrap/app.php';
\$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Drop all calculator-related constraints
\$constraints = DB::select(\"
    SELECT con.conname
    FROM pg_constraint con
    INNER JOIN pg_class rel ON rel.oid = con.conrelid
    WHERE con.contype = 'c'
    AND rel.relname = 'service_types'
    AND pg_get_constraintdef(con.oid) LIKE '%calculator%'
\");

foreach (\$constraints as \$c) {
    echo 'Dropping: ' . \$c->conname . PHP_EOL;
    DB::statement('ALTER TABLE service_types DROP CONSTRAINT IF EXISTS \"' . \$c->conname . '\"');
}

echo 'Done dropping constraints' . PHP_EOL;
"

echo "--- Running migrations ---"
sudo k3s kubectl exec "$POD" -- php /app/artisan migrate --force

echo "=== DONE ==="
