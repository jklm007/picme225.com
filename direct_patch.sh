#!/bin/bash
POD=$(sudo k3s kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}')
echo "=== Pod: $POD ==="

# Check all env vars visible to PHP in the pod
echo "--- All DB-related env vars PHP can see ---"
sudo k3s kubectl exec "$POD" -- php -r '
$keys = ["DB_CONNECTION","DB_HOST","DB_PORT","DB_DATABASE","DB_USERNAME","DB_PASSWORD","DB_URL","DATABASE_URL"];
foreach($keys as $k) {
    echo $k . "=getenv:" . var_export(getenv($k), true) . " ENV:" . var_export($_ENV[$k] ?? "N/A", true) . "\n";
}'

echo ""
echo "--- Testing direct PDO connection to postgres-service ---"
sudo k3s kubectl exec "$POD" -- php -r '
try {
    $pdo = new PDO("pgsql:host=postgres-service;port=5432;dbname=picme_db", "picme_user", "secret_password");
    echo "PDO CONNECTION: SUCCESS\n";
} catch(Exception $e) {
    echo "PDO CONNECTION FAILED: " . $e->getMessage() . "\n";
}'

echo ""
echo "--- Patching config/database.php in the pod ---"
sudo k3s kubectl exec "$POD" -- bash -c "
sed -i \"s/'default' => env('DB_CONNECTION', 'mysql')/'default' => 'pgsql'/\" /app/config/database.php &&
echo 'default set to pgsql' &&
grep \"'default'\" /app/config/database.php | head -1
"

echo "--- Patching pgsql host/db/user/password ---"
sudo k3s kubectl exec "$POD" -- bash -c "
sed -i \"s/'host'      => env('DB_HOST', '127.0.0.1')/'host'      => 'postgres-service'/\" /app/config/database.php &&
sed -i \"s/'port'      => env('DB_PORT', '5432')/'port'      => '5432'/\" /app/config/database.php &&
sed -i \"s/'database'  => env('DB_DATABASE', 'forge')/'database'  => 'picme_db'/\" /app/config/database.php &&
sed -i \"s/'username'  => env('DB_USERNAME', 'forge')/'username'  => 'picme_user'/\" /app/config/database.php &&
sed -i \"s/'password'  => env('DB_PASSWORD', '')/'password'  => 'secret_password'/\" /app/config/database.php &&
echo 'pgsql patched'
"

echo "--- Verifying patch ---"
sudo k3s kubectl exec "$POD" -- grep -A 10 "'pgsql'" /app/config/database.php | head -15

echo "--- Running migrations ---"
sudo k3s kubectl exec "$POD" -- php /app/artisan migrate --force

echo "=== DONE ==="
